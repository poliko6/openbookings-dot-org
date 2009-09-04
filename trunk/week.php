<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	week.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	// finds monday of the selected week & year
	$current_day = strtotime("01/01/" . $_REQUEST["year"]);
	while(date("w", $current_day) != 1 || date("W", $current_day) != $_REQUEST["week"]) { $current_day += 86400; }
	$monday = date("Y-m-d", $current_day);
	$stamp = strtotime($monday);

	// extracts object info
	$sql  = "SELECT object_name, family_name, activity_start, activity_end, activity_step ";
	$sql .= "FROM rs_data_objects INNER JOIN rs_param_families ON rs_data_objects.family_id = rs_param_families.family_id WHERE object_id = " . $_REQUEST["object_id"] . ";";
	$object = db_query($database_name, $sql, "no", "no"); $object_ = fetch_array($object);
	$object_name = $object_["object_name"]; $object_family = $object_["family_name"];

	// extracts hours of activity start, end and step
	$start_hour = $object_["activity_start"];
	$end_hour = $object_["activity_end"];
	$activity_step = $object_["activity_step"] * 60;

	$activity_start = strtotime("1970-01-01 " . $start_hour);
	$activity_end = strtotime("1970-01-01 " . $end_hour);

	// extracts the bookings for the selected week
	$sql  = "SELECT book_id, book_start, book_end, user_id, misc_info, validated ";
	$sql .= "FROM rs_data_bookings ";
	$sql .= "WHERE object_id = " . $_REQUEST["object_id"] . " ";
	$sql .= "AND ((book_start >= '" . date("Y-m-d", $stamp) . "' ";
	$sql .= "AND book_start < '" . date("Y-m-d", ($stamp + 604800)) . "') ";
	$sql .= "OR (book_end >= '" . date("Y-m-d", $stamp) . "' ";
	$sql .= "AND book_end < '" . date("Y-m-d", ($stamp + 604800)) . "') ";
	$sql .= "OR (book_start <= '" .  date("Y-m-d", $stamp) . "' ";
	$sql .= "AND book_end >= '" . date("Y-m-d", $stamp) . "'));";

	$bookings = db_query($database_name, $sql, "no", "no");

	// extracts colors from the table which holds parameters
	// the function "param_extract()" is implemented in the file "functions.php"
	$free_color = param_extract("free_color");
	$validated_color = param_extract("validated_color");
	$unvalidated_color = param_extract("unvalidated_color");

	$screen_width = 810; // automatic screen width recognition was buggy with dual-screen mode
	$screen_height = (intval($_REQUEST["screen_height"]) - 350);
	$column_offset = 20;
	$line_offset = 3;
	$vertical_offset = 50;

	// calculates the width of one week column in pixels
	$column_width = intval(($screen_width - $column_offset * 7) / 7);

	// calculates the height of one week column in pixels
	$coef = intval(($activity_end - $activity_start) / $screen_height);
	$step_size = intval($activity_step/$coef);

	$hour_size = ($step_size / $activity_step) * 3600;

	$blabla_offset = $vertical_offset + ($activity_end - $activity_start) / $activity_step * ($step_size + $line_offset) + 15;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title><?php echo $app_title . " :: " . $object_family . " / " . $object_name . " - " . Translate("Week #", 1) . " " . $_REQUEST["week"]; ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

</head>

<body>

<?php

	$html = "<div id=\"title\" style=\"font-size:24px; top:" . ($vertical_offset - 40) . "px\">" . $object_family . " / " . $object_name . " - " . Translate("Week #", 1) . " " . $_REQUEST["week"] . "</div>";

	$n = -1;

	for($hour=$activity_start;$hour<=$activity_end;$hour+=3600) { $n++;

		//caption : hours
		$top = ($hour_size + $line_offset * $hour_size/$step_size) * $n + $vertical_offset + 5;
		$html .= "<div style=\"font-size:12px; width:30px; left:10px; height:" . $step_size . "px; top:" . $top . "px;\">" . date("H:i",$hour) . "</div>\n";

		$html .= "<div style=\"font-size:2px; width:5px; left:40px; height:1px; top:" . ($top + 7) . "px; border-top: 1px solid black\"></div>\n";
	}

	for($day=1;$day<=7;$day++) {

		$left = ($day - 1) * ($column_width + $column_offset) + 50;

		//caption : days names
		$html .= "<div style=\"font-weight:bold; left:" . $left . "; top:" . ($vertical_offset - 5) . "px;\">" . Translate(date("l", strtotime($monday) + 86400 * ($day - 1)), 1) . "</div>\n";

		$n = -1;

		for($hour=$activity_start;$hour<$activity_end;$hour+=$activity_step) { $n++;

			$step_id = $stamp + ($day - 1) * 86400 + $hour;

			// hour boxes
			$top = ($step_size + $line_offset) * $n + $vertical_offset + 14;
			$html .= "<div id=\"step_" . $step_id . "\" style=\"font-size:8px; height:" . $step_size . "px; width:" . $column_width . "px; top:" . $top . "px; left:" . $left . "px; background:#" . $free_color . "\"></div>\n";
		}
	}

	echo $html;

	echo "<script type=\"text/javascript\"><!--\n\n";

	$n = 0; $div = ""; $color = "";

	if($bookings) {

	$lightcolor = $validated_color;

	$darkred = (hexdec(substr($lightcolor, 1, 2)) - 48);
	$darkgreen = (hexdec(substr($lightcolor, 3, 2)) - 48);
	$darkblue = (hexdec(substr($lightcolor, 5, 2)) - 48);

	if($darkred < 0) { $darkred = 0; }
	if($darkgreen < 0) { $darkgreen = 0; }
	if($darkblue < 0) { $darkblue = 0; }

	$darkred = dechex($darkred); $darkgreen = dechex($darkgreen); $darkblue = dechex($darkblue);

	if(strlen($darkred) == 1) { $darkred = "0" . $darkred; }
	if(strlen($darkgreen) == 1) { $darkgreen = "0" . $darkgreen; }
	if(strlen($darkblue) == 1) { $darkblue = "0" . $darkblue; }

	$darkcolor = "#" . $darkred . $darkgreen . $darkblue;

		while($bookings_ = fetch_array($bookings)) { $n++;

			if($color == $lightcolor) { $color = $darkcolor; } else { $color = $lightcolor; }

			// extracts booker's name
			$sql  = "SELECT last_name, first_name ";
			$sql .= "FROM rs_data_users WHERE user_id = " . $bookings_["user_id"] . ";";

			$booker = db_query($database_name, $sql, "no", "no");

			if($booker) {
				$booker_ = fetch_array($booker);
				$booking_info = $booker_["first_name"] . " " . $booker_["last_name"] . "<br>";
				$booking_info .= stripslashes($bookings_["misc_info"]);
			} else {
				$booking_info = stripslashes($bookings_["misc_info"]);
			}

			for($day=1;$day<=7;$day++) {

				$m = 0;
				$left = ($day - 1) * ($column_width + $column_offset) + 50;

				$start = 0; $end = 0;

				// conputes the size of the coloured range
				for($t=$activity_start; $t<$activity_end; $t+=$activity_step) { $m++;

					$step_id = $stamp + (($day-1) * 86400) + $t + 3600;

					if($step_id >= strtotime($bookings_["book_start"]) && $step_id < strtotime($bookings_["book_end"])) {
						if($start == 0) { $start = $m; } else { $end = $m; }
					}
				}

				$top = ($step_size + $line_offset) * $start + $vertical_offset - $step_size + $line_offset + 7;
				$height = ($step_size + $line_offset) * ($end - $start + 1) - 1;

				// draws the coloured range over the scale to show the booking
				if($start != 0) { $div .= "<div valign=\"center\" id=\"booking_" . $n . "\" style=\"font-size:12px; vertical-align:center;background:#" . $color . "; top:" . $top . "px; left:" . $left . "px; width:" . $column_width . "px; height:" . $height . "\">" . $booking_info . "</div>\n"; }
			} // for
		} // while
	} // if

	echo "--></script>\n";

	echo $div;
	echo "<div id=\"book_details\" class=\"info\"></div>";
?>

<div id="print_date" style="top:<?php echo $blabla_offset; ?>px;left:50px"><?php echo Translate("Printed", 1); ?> <?php echo date($date_format . " h:i"); ?> - <?php echo Translate("You are invited to check for changes on the web site", 1); ?>.</div>

</body>

</html>
