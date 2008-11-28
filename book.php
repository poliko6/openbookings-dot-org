<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	book.php - This file is part of OpenBookings.org (http://www.openbookings.org)

    OpenBookings.org is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    OpenBookings.org is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenBookings.org; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA */

	require_once "config.php";
	require_once "connect_db.php";
	require_once "functions.php";

	function seconds_to_time($seconds) {

		// extract the hours
		$hours = intval($seconds / 3600);

		// extract the minutes
		$minutes = intval(($seconds - ($hours * 3600)) / 60);

		if(strlen($minutes) == 1) { $minutes = "0" . $minutes; }

		return $hours . ":" . $minutes;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title; ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<?php

	$sql = "SELECT booking_method, activity_start, activity_end, activity_step FROM rs_data_objects WHERE object_id = " . $_REQUEST["object_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

	$booking_method = $temp_["booking_method"];
	$start_hour = $temp_["activity_start"];
	$end_hour = $temp_["activity_end"];
	$activity_step = $temp_["activity_step"] * 60;

	if(isset($_REQUEST["start"]) && $_REQUEST["start"] != "") { // page was opened from availables.php (only this page throws the variable "start")
		$start = $_REQUEST["start"];
		$stamp = strtotime(date("Y-m-d", $_REQUEST["start"]));
	} else { // page was opened from day.php
		$start = "";
		$stamp = $_REQUEST["stamp"];
	}

	$error_msg = ""; $script = ""; $update_status = "disabled";

	if(isset($_REQUEST["action_"])) {

		$booking_start = DateAndHour(DateReformat($_REQUEST["start_date"]), $_REQUEST["start_hour"]);

		switch($booking_method) {

			case "time_based":
			$booking_end = DateAndHour(DateReformat($_REQUEST["end_date"]), $_REQUEST["end_hour"]);
			break;

			case "stacking":
			$timestamp_duration = strtotime("1970-01-01 " . $_REQUEST["book_duration"]); // duration in seconds
			$booking_end = date("Y-m-d H:i:s", strtotime($booking_start) + $timestamp_duration);

		} //switch

		switch($_REQUEST["action_"]) {

			case "insert_new_booking":

			$error_msg = checkBooking(0, $_REQUEST["object_id"], $booking_start, $booking_end);

			if($error_msg == "") {
				if(isset($_REQUEST["validated"])) { $validated = 1; } else { $validated = 0; }
				AddBooking("insert", 0, $_REQUEST["booker_id"], $_REQUEST["object_id"], $booking_start, $booking_end, $_REQUEST["misc_info"], $validated);
			}

			break;

			case "update_booking":

			$booking_start = DateAndHour(DateReformat($_REQUEST["start_date"]), $_REQUEST["start_hour"]);
			$booking_end = DateAndHour(DateReformat($_REQUEST["end_date"]), $_REQUEST["end_hour"]);
			$error_msg = checkBooking($_REQUEST["book_id"], $_REQUEST["object_id"], $booking_start, $booking_end);

			if($error_msg == "") {

				if(getObjectInfos($_REQUEST["object_id"], "current_user_is_manager") || $_COOKIE["bookings_profile_id"] == "4") {

					// if the current user is an administrator or manager of the current object
					if(isset($_REQUEST["validated"])) { $validated = 1; } else { $validated = 0; }

				} else {

					if(isset($_REQUEST["validated"])) {

						$validated = 1;

					} else {

						if(getObjectInfos($_REQUEST["object_id"], "is_managed")) {

							// checks if any date of the current booking was changed
							$sql = "SELECT book_start, book_end, validated FROM rs_data_bookings WHERE book_id = " . $_REQUEST["book_id"] . ";";
							$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

							if($temp_["book_start"] != DateAndHour(DateReformat($_REQUEST["start_date"]), $_REQUEST["start_hour"]) || $temp_["book_end"] != DateAndHour(DateReformat($_REQUEST["end_date"]), $_REQUEST["end_hour"])) {
								// at least one date was changed, booking must be re-validated if the object is managed by someone
								$validated = 0;
							} else {
								// no date changes, the booking validation remains in its previous state
								$validated = $temp_["validated"];
							}

						} else {

							// no manager, the booking is always validated
							$validated = 1;
						}
					} // if
				} // if

				AddBooking("update", $_REQUEST["book_id"], $_REQUEST["booker_id"], $_REQUEST["object_id"], $booking_start, $booking_end, $_REQUEST["misc_info"], $validated);

			} // if

			break;

			case "delete_booking":
			$sql = "delete from rs_data_bookings WHERE book_id = " . $_REQUEST["book_id"] . ";";
			db_query($database_name, $sql, "no", "no");

		} // switch

		if($error_msg == "") { // no error, the booking was saved

			echo "<script type=\"text/javascript\"><!--" . chr(10);
			//echo "document.location = \"calendar.php?stamp=" . $stamp . "&object_id=" . $_REQUEST["object_id"] . "\";" . chr(10);
			echo "--></script>" . chr(10);
			echo "</head>" . chr(10);
			echo "<body>" . chr(10);
			echo "</html>";

		} else { // an error has occured (typically the new booking covers another one)
			echo "</head>" . chr(10);
			echo "<body style=\"text-align:center\"><center>" . chr(10);
			echo "<table style=\"text-align:center\">" . chr(10);
			echo "<tr><td style=\"height:60px\"></td></tr>" . chr(10);
			echo "<tr><td>$error_msg</td></tr>" . chr(10);
			echo "<tr><td style=\"height:20px\"></td></tr>" . chr(10);
			echo "<tr><td><button onClick=\"window.history.back();\">" . Translate("Back", 1) . "</button></td></tr>";
			echo "</center></body>" . chr(10);
		}

	} else { // !isset($_REQUEST["action_"])

		// extracts infos about current user
		$sql = "SELECT first_name, last_name, email ";
		$sql .= "FROM rs_data_users ";
		$sql .= "WHERE user_id = " . $_COOKIE["bookings_user_id"] . ";";
		$user = db_query($database_name, $sql, "no", "no"); $user_ = fetch_array($user);

		$user_name = $user_["first_name"] . " " . $user_["last_name"];
		$user_email = $user_["email"];

		// extracts infos about current object
		$sql  = "SELECT rs_param_families.family_name, rs_data_objects.object_name ";
		$sql .= "FROM rs_data_objects INNER JOIN rs_param_families ON rs_data_objects.family_id = rs_param_families.family_id ";
		$sql .= "WHERE rs_data_objects.object_id = " . $_REQUEST["object_id"] . ";";
		$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

		$family_name = $temp_["family_name"]; $object_name = $temp_["object_name"];

		if(getObjectInfos($_REQUEST["object_id"], "is_managed")) {
			$validated = 0;
		} else {
			$validated = 1;
		}

		if($_REQUEST["book_id"] != "0") { // existing booking opened for update

			$booking_action = "Booking update";
			$title = $app_title . " :: " . Translate($booking_action, 1);

			// extracts booking info
			$sql  = "SELECT book_date, user_id, book_start, book_end, validated, misc_info ";
			$sql .= "FROM rs_data_bookings WHERE book_id = " . $_REQUEST["book_id"] . ";";
			$booking = db_query($database_name, $sql, "no", "no"); $booking_ = fetch_array($booking);

			$stamp = strtotime($booking_["book_start"]);

			$book_start_day = date($date_format, strtotime($booking_["book_start"]));
			$book_end_day = date($date_format, strtotime($booking_["book_end"]));

			$book_start_hour = strtotime(date("Y-m-d H:i:s", strtotime($booking_["book_start"]))) - strtotime(date("Y-m-d", strtotime($booking_["book_start"]))) - 3600 - $time_offset;
			$book_end_hour = strtotime(date("Y-m-d H:i:s", strtotime($booking_["book_end"]))) - strtotime(date("Y-m-d", strtotime($booking_["book_end"]))) - 3600 - $time_offset;

			$book_duration = strtotime($booking_["book_start"]) - strtotime($booking_["book_end"]);

			$booker_id = $booking_["user_id"];
			$misc_info = stripslashes($booking_["misc_info"]);
			$validated = $booking_["validated"];
			$action_ = "update_booking";

			if($booker_id == $_COOKIE["bookings_user_id"] || getObjectInfos($_REQUEST["object_id"], "current_user_is_manager") || $_COOKIE["bookings_profile_id"] == "4") { $update_status = ""; }

		} else { // new booking

			$booking_action = "New booking";
			$title = $app_title . " :: " . Translate($booking_action, 1);

			if(isset($_REQUEST["stamp"])) { // from day.php

				$book_start_day = date($date_format, $stamp);
				$book_end_day = date($date_format, $stamp);

				$book_start_hour = strtotime(date("1970-01-01 H:i:s", $stamp));
				$book_end_hour = strtotime(date("1970-01-01 H:i:s", $stamp)) + $activity_step;

				$book_duration = $book_end_hour - $book_start_hour;

			} else { // from availables.php

				$book_start_day = date($date_format, $_REQUEST["start"]);
				$book_end_day = date($date_format, $_REQUEST["end"]);

				$book_start_hour = strtotime(date("1970-01-01 H:i:s", $_REQUEST["start"]));
				$book_end_hour = strtotime(date("1970-01-01 H:i:s", $_REQUEST["end"]));

				$book_duration = strtotime(date("Y-m-d H:i:s", $_REQUEST["end"])) - strtotime(date("Y-m-d H:i:s", $_REQUEST["start"]));
			}

			$misc_info = "";
			$action_ = "insert_new_booking";
			$update_status = "";
		}

		// constructs hours list
		if($start_hour == "00:00" && $end_hour == "00:00") {

			$activity_start = strtotime("1970-01-01 00:00:00");
			$activity_end = strtotime("1970-01-01 23:59:59");

		} else {

			// constructs hours list
			$activity_start = strtotime("1970-01-01" . " " . $start_hour);
			$activity_end = strtotime("1970-01-01" . " " . $end_hour);
		}

		$hours_list = "";

		for($h=$activity_start;$h<=$activity_end;$h+=$activity_step) {
			$hours_list .= "<option value=\"" . $h . "\">" . date("H:i", $h) . "</option>" . chr(10);
		}

		// extracts users list
		$sql =  "SELECT user_id, first_name, last_name, email ";
		$sql .= "FROM rs_data_users WHERE login <> 'anyone' ORDER BY last_name, first_name;";
		$users = db_query($database_name, $sql, "no", "no");

		$users_list = "";

		while($users_ = fetch_array($users)) {

			if(isset($booker_id)) { $id = $booker_id; } else { $id = $_COOKIE["bookings_user_id"]; }

			if($users_["user_id"] == $id) { $selected = " selected"; } else { $selected = ""; }

			$users_list .= "<option value=\"" . $users_["user_id"] . "\"" . $selected . ">";
			$users_list .= unDuplicateName($users_["first_name"], $users_["last_name"]);
			$users_list .= "</option>";
		}

		$book_id = $_REQUEST["book_id"];
		$object_id = $_REQUEST["object_id"];

		$managers_names = getObjectInfos($object_id, "managers_names");

		if($managers_names != "") {
			$managers_names = Translate("managed by", 1) . " " . $managers_names;
		} else {
			$managers_names = Translate("not managed", 1);
		}
?>

<script type="text/javascript"><!--

	function DelBooking() {
		if(window.confirm("<?php echo Translate("Do you really want to delete this booking ?", 0); ?>")) {
			document.location = "book.php?action_=delete_booking&book_id=<?php echo $book_id; ?>&stamp=<?php echo $stamp; ?>&object_id=<?php echo $object_id; ?>";
		}
	}

	function showAvailableSlots() {

		if(document.getElementById("start_date").value != "" && document.getElementById("start_hour").value != "" && document.getElementById("book_duration").value != "") {
			document.getElementById("iframe_action").src = "actions.php?action_=show_available_slots&object_id=<?php echo $_REQUEST["object_id"]; ?>&start_date=" + document.getElementById("start_date").value + "&start_hour=" + document.getElementById("start_hour").value + "&book_duration=" + document.getElementById("book_duration").value;
		} else {
			<?php
				$message  = Translate("Start date, start hour and booking duration are required to compute available slots", 1) . ".";
				$message .= "\\n";
				$message .= Translate("Please, fill in the corresponding form fields an try again", 1) . ".";
				echo "alert(\"" . $message . "\");\n";
			?>
		}
	}

--></script>

</head>

<body>

<form id="form_ajout_resa" name="form_ajout_resa" method="post" action="book.php">

<div class="global" style="width:520px; height:250px; top:50px">

	<table><tr><td>

		<span class="big_text"><?php echo Translate($booking_action, 1); ?></span>
		<br>

		<span style="font-weight:bold"><?php echo $family_name . " / " . $object_name . "</span> <span class=\"small_text\">(" . $managers_names . ")</span>"; ?></td>


	</td></tr><tr><td>

		<table class="table1"><tr><td style="padding:10px">

			<table class="table3">

			<tr><td colspan="3" style="height:10px"></td></tr>

			<tr><td colspan="3" style="text-align:center"><center>

			<?php if(getObjectInfos($_REQUEST["object_id"], "object_is_managed") || intval($_COOKIE["bookings_profile_id"]) > 3) { ?>
				<table class="table3"><tr>
				<td style="font-weight:bold; text-align:right;"><?php echo Translate("Booker", 1); ?> :</td>
				<td><select id="booker_id" name="booker_id"><?php echo $users_list; ?></select></td>
				<td style="width:10px"></td>
				<td><input type="checkbox" id="validated" name="validated" <?php if($validated) { echo "checked"; } ?>> <?php echo Translate("Request validated", 1); ?></td>
				</tr></table>
			<?php } else { ?>
				<table class="table3"><tr>
				<td style="font-weight:bold; text-align:right;"><?php echo Translate("Booker", 1); ?> :</td>
				<td><select disabled><?php echo $users_list; ?></select><input type="hidden" id="booker_id" name="booker_id" value="<?php echo $_COOKIE["bookings_user_id"]; ?>"></td>
				</tr></table>
			<?php } ?>

			</center></td></tr>

			<tr><td colspan="4">

				<?php switch($booking_method) { case "time_based": ?>

				<?php echo Translate("Start", 1); ?> :<br>
				<input type="text" id="start_date" name="start_date" style="width:80px" value="<?php echo $book_start_day; ?>">
				<select id="start_hour" name="start_hour"><?php echo $hours_list; ?></select>

				<?php echo Translate("End", 1); ?> :<br>
				<input type="text" id="end_date" name="end_date" style="width:80px" value="<?php echo $book_end_day; ?>">
				<select id="end_hour" name="end_hour"><?php echo $hours_list; ?></select>

				<?php break; case "stacking": ?>

					<table class="table3">
						<tr>
							<td style="font-weight:bold"><?php echo Translate("Start", 1); ?> :<br><input type="text" id="start_date" name="start_date" style="text-align:center; width:80px" value="<?php echo $book_start_day; ?>"></td>
							<td valign="bottom"><select id="start_hour" name="start_hour"><?php echo $hours_list; ?></select></td>
							<td style="font-weight:bold"><?php echo Translate("Duration", 1); ?> :<br><input type="text" id="book_duration" name="book_duration" style="width:80px; text-align:center" value="<?php echo seconds_to_time($book_duration); ?>"></td>
							<td valign="bottom"><button type="button" onClick="showAvailableSlots()"><?php echo Translate("Show available slots", 1); ?></button></td>
						</tr>

					</table>

				<?php } ?>

			</td></tr>

			<tr><td id="available_slots" colspan="4"></td></tr>

			<tr><td style="font-weight:bold" colspan="4">

			<?php echo Translate("Remarks", 1); ?><br>
			<textarea id="misc_info" name="misc_info" style="width:410px; height:60px"><?php echo $misc_info; ?></textarea>

			</td></tr></table>

		</td></tr></table>

		<br><center>

		<table class="table3"><tr>
		<td><button type="submit" style="width:100px" <?php echo $update_status; ?>><?php echo Translate("OK", 1); ?></button></td>

		<?php if($action_ == "update_booking") { ?>
			<td style="width:20px"></td>
			<td><button type="button" style="width:100px" onCLick="DelBooking()" <?php echo $update_status; ?>><?php echo Translate("Delete", 1); ?></button></td>
		<?php } ?>
		</tr></table>

		</center>

	</td></tr></table>

</div>

<input type="hidden" id="action_" name="action_" value="<?php echo $action_; ?>">
<input type="hidden" id="book_id" name="book_id" value="<?php echo $_REQUEST["book_id"]; ?>">
<input type="hidden" id="stamp" name="stamp" value="<?php echo $stamp; ?>">
<input type="hidden" id="start" name="start" value="<?php echo $start; ?>">
<input type="hidden" id="object_id" name="object_id" value="<?php echo $_REQUEST["object_id"]; ?>">
<input type="hidden" id="object_name" name="object_name" value="<?php echo $object_name; ?>">
<input type="hidden" id="activity_step" name="activity_step" value="<?php echo $activity_step; ?>">

<input type="hidden" id="user_name" name="user_name" value="<?php echo $user_name; ?>">
<input type="hidden" id="user_email" name="user_email" value="<?php echo $user_email; ?>">

</form>

<iframe id="iframe_action"></iframe>

<script type="text/javascript"><!--
document.getElementById("start_hour").value = <?php echo $book_start_hour; ?>;
<?php
	if($booking_method == "time_based") { echo "document.getElementById(\"end_hour\").value = \"" . $book_end_hour . "\";\n"; }
	if(isset($booker_id)) { echo "document.getElementById(\"booker_id\").value = " . $booker_id . ";\n"; }
?>
--></script>

</body>

</html>

<?php } ?>