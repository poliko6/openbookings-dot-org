<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	menu.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	$update_status = "disabled";

	CheckCookie(); // Resets app to the index page if timeout is reached. This function is implemented in functions.php

	if(isset($_POST["action_"])) {

		$booking_start = dateFormat($_POST["start_date"], "", "Y-m-d") . " " . $_POST["start_hour"] . "00";
		$booking_duration = toSeconds($_POST["duration_days"], $_POST["duration_hours"], $_POST["duration_minutes"]);
		$booking_end = date("Y-m-d H:i:s", strtotime("+" . $booking_duration . " seconds", $booking_start));

		switch($_POST["action_"]) {

			case "insert_booking":

			insertBooking("insert", $_POST["book_id"], $_POST["booker_id"], $_POST["object_id"], $booking_start, $booking_end, $_POST["misc_info"], $validated);

			break;

			case "update_booking":

			insertBooking("update", $_POST["book_id"], $_POST["booker_id"], $_POST["object_id"], $booking_start, $booking_end, $_POST["misc_info"], $validated);

			break;
		}

	} else {

	$sql = "SELECT activity_start, activity_end, activity_step FROM rs_data_objects WHERE object_id = " . $_REQUEST["object_id"] . ";";
	$object = db_query($database_name, $sql, "no", "no"); $object_ = fetch_array($object);

	$activity_step = $object_["activity_step"];

	if($_REQUEST["book_id"] == "0") {

		$booking_action = "New booking";

		$start_date = date($date_format, $_GET["stamp"]);
		$start_hour = date("H:i", $_GET["stamp"]);
		$misc_info = "";

		$array_duration = getDuration($activity_step * 60);

		$action_ = "insert_booking";
		$update_status = "";

	} else {

		$booking_action = "Edit booking";

		$sql  = "SELECT user_id, book_start, book_end, validated, misc_info ";
		$sql .= "FROM rs_data_bookings ";
		$sql .= "WHERE book_id = " . $_REQUEST["book_id"] . " ";
		$sql .= "AND object_id = " . $_REQUEST["object_id"] . ";";
		$booking = db_query($database_name, $sql, "no", "no"); $booking_ = fetch_array($booking);

		$booker_id = $booking_["user_id"];
		$start_date = date($date_format, strtotime($booking_["book_start"]));
		$start_hour = date("H:i", strtotime($booking_["book_start"]));
		$misc_info = $booking_["misc_info"];

		$array_duration = getDuration(strtotime($booking_["book_end"]) - strtotime($booking_["book_start"]));

		$action_ = "update_booking";
		if($booker_id == $_COOKIE["bookings_user_id"] || getObjectInfos($_REQUEST["object_id"], "current_user_is_manager") || $_COOKIE["bookings_profile_id"] == "4") { $update_status = ""; }
	}

	$duration_days = $array_duration["days"];
	$duration_hours = $array_duration["hours"];
	$duration_minutes = $array_duration["minutes"];
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<title><?php echo $app_title; ?></title>

	<link rel="stylesheet" type="text/css" href="styles.php">

	<style type="text/css">
		.date { text-align:center; width:100px }
		.duration { text-align:center; width:50px }
		#iframe_action { visibility:visible; width:500px; height:200px }
	</style>

	<script type="text/javascript"><!--

		<?php includeCommonScripts(); ?>

		function showFirstAvailability() {
			$("iframe_action").src = "actions.php?action_=show_first_availability&object_id=<?php echo $_REQUEST["object_id"]; ?>&start_date=" + $("start_date").value + "&start_hour=" + $("start_hour").value + "&duration=" + $("duration_days").value + "|" + $("duration_hours").value + "|" + $("duration_minutes").value;
		}

		function DelBooking() {
			if(window.confirm("<?php echo Translate("Do you really want to delete this booking ?", 0); ?>")) {
				$("iframe_action").src = "actions.php?action_=delete_booking&book_id=<?php echo $_GET["book_id"]; ?>&object_id=<?php echo $_REQUEST["object_id"]; ?>";
			}
		}

	--></script>

</head>

<body style="text-align:left; margin:10px">

	<form id="main_form" name="main_form" method="post" action="actions.php" target="iframe_action">

	<div class="global" style="width:440px;top:50px">

	<span class="big_text"><?php echo Translate($booking_action, 1); ?></span>
	<br>

	<center>

	<div class="colorframe" style="padding:10px">

	<table class="table3">

		<tr>
			<td colspan="3" style="font-weight:bold"><?php echo Translate("Start", 1); ?></td>
			<td rowspan="2" style="width:50px"></td>
			<td colspan="3" style="font-weight:bold"><?php echo Translate("Duration", 1); ?></td>
		</tr><tr>
			<td colspan="2"><?php echo Translate("Date", 1); ?><br><input class="date" type="text" id="start_date" name="start_date" value="<?php echo $start_date; ?>"></td>
			<td><?php echo Translate("Hour", 1); ?><br><input class="duration" type="text" id="start_hour" name="start_hour" value="<?php echo $start_hour; ?>"></td>
			<td><?php echo Translate("Days", 1); ?><br><input class="duration" type="text" id="duration_days" name="duration_days" value="<?php echo $duration_days; ?>"></td>
			<td><?php echo Translate("Hours", 1); ?><br><input class="duration" type="text" id="duration_hours" name="duration_hours" value="<?php echo $duration_hours; ?>"></td>
			<td><?php echo Translate("Minutes", 1); ?><br><input class="duration" type="text" id="duration_minutes" name="duration_minutes" value="<?php echo $duration_minutes; ?>"></td>
		</tr><tr>

			<td colspan="7" style="font-weight:bold; padding-top:20px"><?php echo Translate("Remarks", 1); ?><textarea id="misc_info" name="misc_info" style="width:410px; height:60px"><?php echo $misc_info; ?></textarea></td>
		</tr><tr>
			<td colspan="7" style="font-weight:bold; padding-top:20px"><center><button type="button" onClick="showFirstAvailability()"><?php echo Translate("Show first availability", 1); ?></button></center></td>
		</tr><tr>
			<td colspan="7" id="info_display" style="text-align:center"></td>
		</tr>
	</table>

	</div>

	<br>

	<button type="submit" style="width:100px" <?php echo $update_status; ?>><?php echo Translate("Save", 1); ?></button>
	<button type="button" style="width:100px" onCLick="DelBooking()" <?php echo $update_status; ?>><?php echo Translate("Delete", 1); ?></button>

	</center></div>

	<input type="hidden" name="book_id" value="<?php echo $_REQUEST["book_id"]; ?>">
	<input type="hidden" name="booker_id" value="<?php echo $_REQUEST["booker_id"]; ?>">
	<input type="hidden" name="object_id" value="<?php echo $_REQUEST["object_id"]; ?>">
	<input type="hidden" name="action_" value="<?php echo $action_; ?>">

	</form>

	<iframe id="iframe_action" name="iframe_action"></iframe>

</body>

</html>

<?php } // !action_ ?>
