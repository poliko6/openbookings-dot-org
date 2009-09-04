<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	day.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	CheckCookie(); // Resets app to the index page if timeout is reached. This function is implemented in functions.php
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<?php

	$ok_screen_width = 785; $ok_offset = 10;

	// extracts colors from the table which holds parameters
	// the function "param_extract()" is implemented in the file "functions.php"
	// extracts colors from the table which holds parameters
	$cv_validated_color = checkVar("html", param_extract("validated_color"), "hex", 6, 6, "00c000", "");
	//$cv_unvalidated_color= checkVar("html", param_extract("unvalidated_color"), "hex", 6, 6, "ff8000", "");
	//$cv_background_color = checkVar("html", param_extract("background_color"), "hex", 6, 6, "eff0f8", "");

	$cv_post_object_id = checkVar("sql", $_POST["object_id"], "int", "1", "65535", "0", "");
	$cv_post_stamp = checkVar("sql", $_POST["stamp", "int", "", "", "", "");

	// extracts infos about the selected object
	$sql  = "SELECT rs_param_families.family_name, rs_data_objects.object_name, booking_method, activity_start, activity_end, activity_step ";
	$sql .= "FROM rs_data_objects INNER JOIN rs_param_families ON rs_data_objects.family_id = rs_param_families.family_id ";
	$sql .= "WHERE rs_data_objects.object_id = " . $cv_post_object_id. ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

	$cv_booking_method = checkVar("", $temp_["booking_method"], "string", 8, 10, "time_based", "");
	$cv_start_hour = checkVar("", temp_["activity_start"], "hour", "", "", "09:00", "");
	$cv_end_hour = checkVar("", temp_["activity_end"], "hour", "", "", "17:00", "");
	$cv_activity_step = checkVar("", $temp_["activity_step"], "", "", "15", "") * 60;

	// calculates full timestamp of the activity for the selected day
	if(($cv_start_hour == "00:00" || $cv_start_hour == "0:00" || $cv_start_hour == "") && ($cv_end_hour == "00:00" || $cv_end_hour == "0:00" || $cv_end_hour = "")) {

		// all day long activity (midnight to midnight) is a particular case
		$ok_activity_start = strtotime(date("Y-m-d", $cv_post_stamp) . " 00:00:00");
		$ok_activity_end = strtotime(date("Y-m-d", $cv_post_stamp) . " 23:59:59");

	} else {

		// standard planning with daily break
		$ok_activity_start = strtotime(date("Y-m-d", $cv_post_stamp) . " " . $cv_start_hour);
		$ok_activity_end = strtotime(date("Y-m-d", $cv_post_stamp) . " " . $cv_end_hour);
	}

	// calculates the width of one time step in pixels
	$ok_coef = intval(($ok_activity_end - $ok_activity_start) / $ok_screen_width);
	$ok_step_size = intval($cv_activity_step/$ok_coef);

	// calculates the width of one hour in pixels
	$ok_hour_step_size = $ok_step_size * (60 / $temp_["activity_step"]);
?>

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title><?php echo $app_title . " :: " . date($date_format, $cv_post_stamp); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<style type="text/css">
.hour_tag { top:58px; font-family: arial; font-size:10px; }
.line { top:56px; width:1px; height:3px; font-size:0px; background:black; }
.free_step { cursor:pointer; top:40px; height:15px; width:<?php echo intval($cv_activity_step / $ok_coef) - 3; ?>px; background:#<?php echo $free_color; ?>; font-size:0px; }
</style>

<script type="text/javascript"><!--

	<?php includeCommonScripts(); ?>

	// this function shows a popup near the mouse pointer when overheading the hours drawing

	function ShowInfos(evt, booking_nr) {

		if($("s" + booking_nr).value != "0") { // showing booking info is useless if there is no booking

			var posx = 0;

			// paste booker name in info popup
			var info = "<span style='font-weight:bold'><?php echo Translate("Booked by", 1); ?> : "  + bookings[$("s" + booking_nr).value]['booker_name'] +  "</span>";
			info += "<br>";
			info += bookings[$("s" + booking_nr).value]['booking_dates'];
			info += "<br>";
			info += "<span style='color:#808080'>" + bookings[$("s" + booking_nr).value]['misc_info'] + "</span>";

			$("booking_infos").innerHTML = info;

			if(evt.clientX < <?php echo intval($ok_screen_width/2); ?>) {
				posx = evt.clientX + 20;
			} else {
				posx = evt.clientX - (225 + 20);
			}

			$("booking_infos").style.left = posx;

			$("booking_infos").style.visibility = "visible";
		}
	}

	function HideInfos() {
		$("booking_infos").style.visibility = "hidden";
	}

	function openBooking(stamp) {
		var book_id = $("s" + stamp).value;
		top.frames[1].location = "<?php echo $cv_booking_method; ?>_book.php?book_id=" + book_id + "&object_id=<?php echo $cv_post_object_id; ?>&stamp=" + stamp;
	}

--></script>

</head>

<body>

<span id="title_" style="position:absolute; font-size:20px; top:2px; left:10px"></span>

<?php

	// extracts the bookings for the selected day
	$sql  = "SELECT book_id, book_start, book_end, user_id, misc_info, validated ";
	$sql .= "FROM rs_data_bookings ";
	$sql .= "WHERE object_id = " . $cv_post_object_id. " ";
	$sql .= "AND ((book_start >= '" . date("Y-m-d", $cv_post_stamp) . "' ";
	$sql .= "AND book_start < '" . date("Y-m-d", ($cv_post_stamp + 86400)) . "') ";
	$sql .= "OR (book_end >= '" . date("Y-m-d", $cv_post_stamp) . "' ";
	$sql .= "AND book_end < '" . date("Y-m-d", ($cv_post_stamp + 86400)) . "') ";
	$sql .= "OR (book_start <= '" .  date("Y-m-d", $cv_post_stamp) . "' ";
	$sql .= "AND book_end >= '" . date("Y-m-d", $cv_post_stamp) . "'));";

	$bookings = db_query($database_name, $sql, "no", "no");

	// dispays the timesteps
	$n = -1; for($t=$ok_activity_start; $t<$ok_activity_end; $t+=$cv_activity_step) { $n++;
		echo "<div id=\"step_" . $t . "\" class=\"free_step\" style=\"left:" . (($ok_step_size * $n) + $ok_offset) . "px\" onMouseOver=\"ShowInfos(event, '" . $t . "')\" onMouseOut=\"HideInfos()\" onClick=\"openBooking('" . $t . "')\"><input type=\"hidden\" id=\"s" . $t . "\" value=\"0\"></div>\n";
	}

	// displays the hours digits as a caption
	$n = -1; for($t=$ok_activity_start; $t<=$ok_activity_end; $t=strtotime("+1 hour", $t)) { $n++;
		echo "<div class=\"hour_tag\" style=\"left:" . (($ok_hour_step_size * $n) + $ok_offset - 8) . "px\">" . date("H", $t) . "</div>\n";
		echo "<div class=\"line\" style=\"left:" . (($ok_hour_step_size * $n) + $ok_offset - 2) . "px\"></div>\n";
	}

	// highlight of the booked areas using javascript

	// var bookings[x][y] with following y values :
	// 1 = bookings #id, 2 = booker name, 3 = misc info

	echo "<script type=\"text/javascript\"><!--\n\n";
	echo "var bookings = new Array();\n\n";

	$n = 0;

	while($bookings_ = fetch_array($bookings)) { $n++;

		// extracts booker's name
		$sql  = "SELECT last_name, first_name ";
		$sql .= "FROM rs_data_users WHERE user_id = " . $bookings_["user_id"] . ";";
		$booker = db_query($database_name, $sql, "no", "no"); $booker_ = fetch_array($booker);

		// constructs a javascript array that contains infos about bookings,
		// in order to show details about every bookings without having to query the database

		echo "bookings[" . $bookings_["book_id"] . "] = new Array();\n";
		echo "bookings[" . $bookings_["book_id"] . "]['booker_name'] = \"" . unDuplicateName($booker_["first_name"], $booker_["last_name"]) . "\";\n";
		echo "bookings[" . $bookings_["book_id"] . "]['booking_dates'] = \"" . dateRange($bookings_["book_start"], $bookings_["book_end"]) . "\";\n";

		if($bookings_["misc_info"] == "" || $bookings_["misc_info"] == Chr(13).Chr(10)) { // replace with \n ?
			echo "bookings[" . $bookings_["book_id"] . "]['misc_info'] = \"(" . Translate("no more informations", 1) . ")\";\n\n";
		} else {
			echo "bookings[" . $bookings_["book_id"] . "]['misc_info'] = \"" . str_replace(Chr(13).Chr(10), "<br>", $bookings_["misc_info"]) . "\";\n\n";
		}

		if($bookings_["validated"]) { $ok_book_color = $cv_validated_color; } else { $ok_book_color = $unvalidated_color; }

		for($t=$ok_activity_start; $t<$ok_activity_end; $t+=$cv_activity_step) {

			if($t >= strtotime($bookings_["book_start"]) && $t < strtotime($bookings_["book_end"])) {
				echo "$(\"s" . $t . "\").value = \"" . $bookings_["book_id"] . "\";\n";
				echo "$(\"step_" . $t . "\").style.background = \"#" . $ok_book_color . "\";\n";

				if($t == strtotime($bookings_["book_start"])) {
					echo "$(\"step_" . $t . "\").style.borderLeft = \"1px solid black\";\n";
					echo "$(\"step_" . $t . "\").style.width = '" . (intval($cv_activity_step / $ok_coef) - 4) . "px';\n";
				}


			}
		}
	}

	echo "--></script>\n";

	echo "<div id=\"booking_infos\" style=\"top:20px\"></div>";

	$cv_managers_names = checkVar("html", getObjectInfos($cv_post_object_id, "managers_names"), "string", "", "", "", "");

	if($cv_managers_names != "") {
		$cv_managers_names = Translate("managed by", 1) . " " . $cv_managers_names;
	} else {
		$cv_managers_names = Translate("not managed", 1);
	}

?>

<script type="text/javascript"><!--
	$("title_").innerHTML = "<?php echo date($date_format, $cv_post_stamp); ?> - " + parent.document.getElementById("title_").value + " <span class=\"small_text\">(<?php echo $cv_managers_names; ?>)</span>";
--></script>

</body>

</html>
