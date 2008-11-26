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

	$screen_width = 785; $offset = 10;

	// extracts colors from the table which holds parameters
	// the function "param_extract()" is implemented in the file "functions.php"
	$free_color = param_extract("free_color");
	$validated_color = param_extract("validated_color");
	$unvalidated_color = param_extract("unvalidated_color");

	$sql = "SELECT activity_start, activity_end, activity_step FROM rs_data_objects WHERE object_id = " . $_REQUEST["object_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

	$start_hour = $temp_["activity_start"];
	$end_hour = $temp_["activity_end"];
	$activity_step = $temp_["activity_step"] * 60;

	// calculates full timestamp of the activity for the selected day
	if(($start_hour == "00:00" || $start_hour == "0:00" || $start_hour == "") && ($end_hour == "00:00" || $end_hour == "0:00" || $end_hour = "")) {

		// all day long activity (midnight to midnight) is a particular case
		$activity_start = strtotime(date("Y-m-d", $_REQUEST["stamp"]) . " 00:00:00");
		$activity_end = strtotime(date("Y-m-d", $_REQUEST["stamp"]) . " 23:59:59");

	} else {

		// standard planning with daily break
		$activity_start = strtotime(date("Y-m-d", $_REQUEST["stamp"]) . " " . $start_hour);
		$activity_end = strtotime(date("Y-m-d", $_REQUEST["stamp"]) . " " . $end_hour);
	}

	// calculates the width of one time step in pixels
	$coef = intval(($activity_end - $activity_start) / $screen_width);
	$step_size = intval($activity_step/$coef);

	// calculates the width of one hour in pixels
	$hour_step_size = $step_size * (60 / $temp_["activity_step"]);

	// extracts infos about the selected object
	$sql  = "SELECT rs_param_families.family_name, rs_data_objects.object_name ";
	$sql .= "FROM rs_data_objects INNER JOIN rs_param_families ON rs_data_objects.family_id = rs_param_families.family_id ";
	$sql .= "WHERE rs_data_objects.object_id = " . $_REQUEST["object_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
?>

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . date($date_format, $_REQUEST["stamp"]); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<style type="text/css">
.hour_tag { top:58px; font-family: arial; font-size:10px; }
.line { top:56px; width:1px; height:3px; font-size:0px; background:black; }
.free_step { cursor:pointer; top:40px; height:15px; width:<?php echo intval($activity_step / $coef) - 3; ?>px; background:#<?php echo $free_color; ?>; font-size:0px; }
</style>

<script type="text/javascript"><!--

	// this function shows a popup near the mouse pointer when overheading the hours drawing

	function ShowInfos(evt, booking_nr) {

		if(document.getElementById("s" + booking_nr).value != "0") { // showing booking info is useless if there is no booking

			var posx = 0;

			// paste booker name in info popup
			var info = "<span style='font-weight:bold'><?php echo Translate("Booked by", 1); ?> : "  + bookings[document.getElementById("s" + booking_nr).value]['booker_name'] +  "</span>";
			info += "<br>";
			info += bookings[document.getElementById("s" + booking_nr).value]['booking_dates'];
			info += "<br>";
			info += "<span style='color:#808080'>" + bookings[document.getElementById("s" + booking_nr).value]['misc_info'] + "</span>";

			document.getElementById("booking_infos").innerHTML = info;

			if(evt.clientX < <?php echo intval($screen_width/2); ?>) {
				posx = evt.clientX + 20;
			} else {
				posx = evt.clientX - (225 + 20);
			}

			document.getElementById("booking_infos").style.left = posx;

			document.getElementById("booking_infos").style.visibility = "visible";
		}
	}

	function HideInfos() { document.getElementById("booking_infos").style.visibility = "hidden"; }

	function openBooking(stamp) {
			var book_id = document.getElementById("s" + stamp).value;
			top.frames[1].location = "book.php?book_id=" + book_id + "&object_id=<?php echo $_REQUEST["object_id"]; ?>&stamp=" + stamp;
	}

--></script>

</head>

<body>

<span id="title_" style="position:absolute; font-size:20px; top:2px; left:10px"></span>

<?php

	// extracts the bookings for the selected day
	$sql  = "SELECT book_id, book_start, book_end, user_id, misc_info, validated ";
	$sql .= "FROM rs_data_bookings ";
	$sql .= "WHERE object_id = " . $_REQUEST["object_id"] . " ";
	$sql .= "AND ((book_start >= '" . date("Y-m-d", $_REQUEST["stamp"]) . "' ";
	$sql .= "AND book_start < '" . date("Y-m-d", ($_REQUEST["stamp"] + 86400)) . "') ";
	$sql .= "OR (book_end >= '" . date("Y-m-d", $_REQUEST["stamp"]) . "' ";
	$sql .= "AND book_end < '" . date("Y-m-d", ($_REQUEST["stamp"] + 86400)) . "') ";
	$sql .= "OR (book_start <= '" .  date("Y-m-d", $_REQUEST["stamp"]) . "' ";
	$sql .= "AND book_end >= '" . date("Y-m-d", $_REQUEST["stamp"]) . "'));";

	$bookings = db_query($database_name, $sql, "no", "no");

	// dispays the timesteps
	$n = -1; for($t=$activity_start; $t<$activity_end; $t+=$activity_step) { $n++;
		echo "<div id=\"step_" . $t . "\" class=\"free_step\" style=\"left:" . (($step_size * $n) + $offset) . "px\" onMouseOver=\"ShowInfos(event, '" . $t . "')\" onMouseOut=\"HideInfos()\" onClick=\"openBooking('" . $t . "')\"><input type=\"hidden\" id=\"s" . $t . "\" value=\"0\"></div>" .chr(10);
	}

	// displays the hours digits as a caption
	$n = -1; for($t=$activity_start; $t<=$activity_end; $t+=3600) { $n++;
		echo "<div class=\"hour_tag\" style=\"left:" . (($hour_step_size * $n) + $offset - 8) . "px\">" . date("H", $t) . "</div>\n";
		echo "<div class=\"line\" style=\"left:" . (($hour_step_size * $n) + $offset - 2) . "px\"></div>\n";
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

		if($bookings_["misc_info"] == "" || $bookings_["misc_info"] == Chr(13).Chr(10)) {
			echo "bookings[" . $bookings_["book_id"] . "]['misc_info'] = \"(" . Translate("no more informations", 1) . ")\";\n\n";
		} else {
			echo "bookings[" . $bookings_["book_id"] . "]['misc_info'] = \"" . str_replace(Chr(13).Chr(10), "<br>", $bookings_["misc_info"]) . "\";\n\n";
		}

		if($bookings_["validated"]) { $book_color = $validated_color; } else { $book_color = $unvalidated_color; }

		for($t=$activity_start; $t<$activity_end; $t+=$activity_step) {

			if($t >= strtotime($bookings_["book_start"]) && $t < strtotime($bookings_["book_end"])) {
				echo "document.getElementById(\"s" . $t . "\").value = \"" . $bookings_["book_id"] . "\";\n";
				echo "document.getElementById(\"step_" . $t . "\").style.background = \"#" . $book_color . "\";\n";

				if($t == strtotime($bookings_["book_start"])) {
					echo "document.getElementById(\"step_" . $t . "\").style.borderLeft = \"1px solid black\";\n";
					echo "document.getElementById(\"step_" . $t . "\").style.width = '" . (intval($activity_step / $coef) - 4) . "px';\n";
				}


			}
		}
	}

	echo "--></script>\n";

	echo "<div id=\"booking_infos\" style=\"top:20px\"></div>";

	$managers_names = getObjectInfos($_REQUEST["object_id"], "managers_names");

	if($managers_names != "") {
		$managers_names = Translate("managed by", 1) . " " . $managers_names;
	} else {
		$managers_names = Translate("not managed", 1);
	}

?>

<script type="text/javascript"><!--
	document.getElementById("title_").innerHTML = "<?php echo date($date_format, $_REQUEST["stamp"]); ?> - " + parent.document.getElementById("title_").value + " <span class=\"small_text\">(<?php echo $managers_names; ?>)</span>";
--></script>

</body>

</html>
