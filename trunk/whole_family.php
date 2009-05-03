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

	CheckCookie(); // Resets app to the index page if timeout is reached. This function is implemented in functions.php

	$object_column_width = 250;
	$day_width = 75;
	$cells_height = 24;
	$bookline_height = 15;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

	<title><?php echo Translate("Whole family bookings", 1); ?></title>

	<link rel="stylesheet" type="text/css" href="styles.php">

	<script type="text/javascript"><!--

		function ShowInfos(evt, booker_name, booking_dates, message) {

			// paste message into div
			var info = "<span style='font-weight:bold'><?php echo Translate("Booked by", 1); ?> : " + booker_name + "<\/span>";
			info += "<br>";
			info += booking_dates;
			info += "<br>";
			info += "<span style='color:#808080'>" + message + "<\/span>";

			document.getElementById("booking_infos").innerHTML = info;
			// Set div position near the mouse pointer
			if(evt.clientX < document.getElementById("div_main").style.width / 2) {
				document.getElementById("booking_infos").style.left = (evt.clientX + document.body.scrollLeft) + "px";
			} else {
				document.getElementById("booking_infos").style.left = (evt.clientX - 260 + document.body.scrollLeft) + "px";
			}
			document.getElementById("booking_infos").style.top = (evt.clientY + document.body.scrollTop - 20) + "px";

			// Show div
			document.getElementById("booking_infos").style.visibility = "visible";
		}

		function HideInfos() { document.getElementById("booking_infos").style.visibility = "hidden"; }

	--></script>

</head>

<body style="text-align:left; margin:10px">

<?php

	// the function "param_extract()" is implemented in the file "functions.php"
	$free_color = param_extract("free_color");
	$validated_color = param_extract("validated_color");
	$unvalidated_color = param_extract("unvalidated_color");

	$sql = "SELECT family_name FROM rs_param_families WHERE family_id = '". $_REQUEST["family_id"] . "';";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

	$html = "<span class=\"big_text\">" . $temp_["family_name"] . " - " . Translate("Week #", 1) . " " . $_GET["week"]. "/". $_GET["year"] . "</span><br>";

	// gets monday's date of selected week
	$monday = getMonday($_GET["year"], $_GET["week"]);

	$html .= "<div id=\"div_main\" style=\"width:" . ($object_column_width + $day_width * 7) . "px\">";

	$html .= "<table class=\"table5\" summary=\"\">\n\n";
	$html .= "<tr>\n";
	$html .= "<th style=\"width:" . $object_column_width . "px\">" . Translate("Objects", 1) . "</th>\n";

	for($day_nr=0;$day_nr<=6;$day_nr++) {
		if(date("Y-m-d", $monday + ($day_nr * 86400)) == date("Y-m-d")) { $day_color = "red"; } else { $day_color = "black"; }
		$html .= "<th style=\"width:" . $day_width . "px\">" . substr(Translate(date("l", $monday + (86400 * $day_nr)), 1), 0, 3) . " " . date("d/m", $monday + (86400 * $day_nr)) . "</th>\n";
	}

	$html .= "</tr>\n\n";

	$sql = "SELECT object_id, object_name FROM rs_data_objects WHERE family_id = " . $_GET["family_id"] . ";";
	$objects = db_query($database_name, $sql, "no", "no");

	while($objects_ = fetch_array($objects)) {

		$html .= "<tr>\n";
		$html .= "<td style=\"height:" . $cells_height . "px; width:" . $object_column_width . "px\">&nbsp;" . $objects_["object_name"] . "</td>\n";

		for($day_nr=0;$day_nr<=6;$day_nr++) {

			$current_day_start = $monday + (86400 * $day_nr);

			// extracts the bookings for current object+day
			$sql  = "SELECT book_id, book_start, book_end, validated, rs_data_bookings.misc_info, object_name, first_name, last_name ";
			$sql .= "FROM rs_data_bookings ";
			$sql .= "INNER JOIN rs_data_objects ON rs_data_objects.object_id = rs_data_bookings.object_id ";
			$sql .= "LEFT JOIN rs_data_users ON rs_data_bookings.user_id = rs_data_users.user_id ";
			$sql .= "WHERE rs_data_bookings.object_id = " . $objects_["object_id"] . " ";

			$sql .= "AND ((book_start >= '" . date("Y-m-d", $current_day_start) . "' ";
			$sql .= "AND book_start < '" . date("Y-m-d", ($current_day_start + 86400)) . "') ";

			$sql .= "OR (book_end >= '" . date("Y-m-d", $current_day_start) . "' ";
			$sql .= "AND book_end < '" . date("Y-m-d", ($current_day_start + 86400)) . "') ";

			$sql .= "OR (book_start <= '" .  date("Y-m-d", $current_day_start) . "' ";
			$sql .= "AND book_end >= '" . date("Y-m-d", $current_day_start) . "')) ";

			$sql .= "ORDER BY book_start ASC;";

			$bookings = db_query($database_name, $sql, "no", "no");

			$html .= "<td style=\"text-align:left; height:" . $cells_height . "px; width:" . $day_width . "\">\n";
			$html .= "<div class=\"object_line\" style=\"background:#" . $free_color . "; left:0px; height:" . $bookline_height . "px; width:" . $day_width  . "px\">\n";

			while($bookings_ = fetch_array($bookings)) {

				$booking_user_name = unDuplicateName($bookings_["first_name"], $bookings_["last_name"]);
				if($bookings_["validated"]) { $booking_color = $validated_color; } else { $booking_color = $unvalidated_color; }

				$booking_info = $bookings_["misc_info"];
				if($booking_info == "") { $booking_info = "(" . Translate("No details", 1) . ")"; }

				$booking_duration = strtotime($bookings_["book_end"]) - strtotime($bookings_["book_start"]);

				if(strtotime($bookings_["book_start"]) <= $current_day_start) {
					$booking_start = 0;
				} else {
					$booking_start = strtotime($bookings_["book_start"]) - $current_day_start;
				}

				if(strtotime($bookings_["book_end"]) >= ($current_day_start + 86400)) {
					$booking_end = 86400;
				} else {
					$booking_end = strtotime($bookings_["book_end"]) - $current_day_start;
				}

				$booking_left = round($booking_start / 86400 * $day_width);
				$booking_width = round(($booking_end - $booking_start) / 86400 * $day_width);

				$booking_dates = dateRange($bookings_["book_start"], $bookings_["book_end"]);

				$html .= "<div class=\"booking_line\" style=\"background:#" . $booking_color . "; height:" . $bookline_height . "px; left:" . $booking_left . "px; width:" . $booking_width . "px\" ";
				$html .= "onMouseOver=\"ShowInfos(event,'" . $booking_user_name . "','" . $booking_dates . "','" . $booking_info . "')\" onMouseOut=\"HideInfos()\"></div>\n";

			}

			$html .= "</div>\n";
			$html .= "</td>\n";
		}

		$html .= "</tr>\n";
	}
	$html .= "</table>\n";
	$html .= "</div>\n";


	echo $html;
?>

<div id="booking_infos"></div>

</body>
</html>
