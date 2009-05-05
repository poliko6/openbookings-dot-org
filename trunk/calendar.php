<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	calendar.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	// uses the current year if not specified by the posted vars
	if(isset($_REQUEST["stamp"])) {
		$year = date("Y", $_REQUEST["stamp"]);
	} else {
		if(isset($_REQUEST["annee"])) { $year = $_REQUEST["annee"]; } else { $year = date("Y"); }
	}

	// extracts colors from the table which holds parameters
	// the function "param_extract()" is implemented in the file "functions.php"
	$validated_color = param_extract("validated_color");
	$unvalidated_color = param_extract("unvalidated_color");

	$sql  = "SELECT rs_param_families.family_name, rs_data_objects.object_name ";
	$sql .= "FROM rs_data_objects INNER JOIN rs_param_families ON rs_data_objects.family_id = rs_param_families.family_id ";
	$sql .= "WHERE rs_data_objects.object_id = " . $_REQUEST["object_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

	$family_name = $temp_["family_name"]; $object_name = $temp_["object_name"];

	// extracts current year bookings
	$sql = "SELECT book_id, book_start, book_end FROM rs_data_bookings ";
	$sql .= "WHERE object_id = " . $_REQUEST["object_id"] . " ";
	$sql .= "AND (YEAR(book_start) = " . $year . " ";
	$sql .= "OR YEAR(book_end) = " . $year . ");";
	$reservations = db_query($database_name, $sql, "no", "no");

	// extracts infos about selected object
	$sql  = "SELECT rs_param_families.family_name, rs_data_objects.object_name ";
	$sql .= "FROM rs_data_objects INNER JOIN rs_param_families ON rs_data_objects.family_id = rs_param_families.family_id ";
	$sql .= "WHERE rs_data_objects.object_id = " . $_REQUEST["object_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

	$family_name = $temp_["family_name"]; $object_name = $temp_["object_name"];
?>

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . Translate("Calendar", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
	function ClickOnDay(stamp) { document.getElementById("iframe_day").src = "day.php?stamp=" + stamp + "&object_id=<?php echo $_REQUEST["object_id"]; ?>&screen_width=" + screen.width; }
--></script>

</head>

<body>


<div class="global" style="width:810px">

<iframe id="iframe_day" name="iframe_day" frameborder="0" scrolling="no" style="background:#<?php echo param_extract("background_color"); ?>; height:90px; width:810px;"></iframe>

<center>

<table class="small_text" style="margin-left:6px"><tr>

<?php
	for($month=1;$month<=12;$month++) {
		$day = 1;
?>

<td VALIGN="TOP" style="font-weight:bold; width:62px; padding:0px">

<?php echo Translate(date("F", strtotime($year . "-" . $month . "-" . $day)), 1); ?><br>

<table class="table1">

<?php

	while(checkdate($month, $day, $year)) {

		if(strlen($month) == 1) { $month = "0" . $month; }
		if(strlen($day) == 1) { $day = "0" . $day; }

		$date_en_cours = $year . "-" . $month . "-" . $day;

		$day_name = Translate(date("l", strtotime($date_en_cours)), 1);

		$range_start = $date_en_cours;
		$range_end = date("Y-m-d", strtotime($date_en_cours) + 86400);

		if(date("l", strtotime($date_en_cours)) == "Saturday" || date("l", strtotime($date_en_cours)) == "Sunday") {
			$couleur = "#7f7fff";
			$ferie = 0;
		} else {
			$couleur = "#efefef";
			$ferie = 1;
		}
?>

<tr id="j<?php echo strtotime($date_en_cours); ?>" style="background:<?php echo $couleur;?>; cursor: pointer" onClick="ClickOnDay(<?php echo strtotime($date_en_cours); ?>)">
<td style="width:20px; text-align:center"><?php echo date("d", strtotime($date_en_cours)); ?></td>
<td style="width:50px"><?php echo substr($day_name, 0, 3); ?><input type="hidden" id="s<?php echo strtotime($date_en_cours); ?>" name="s<?php echo strtotime($date_en_cours); ?>" value="<?php if(date("l", strtotime($date_en_cours)) == "Saturday" || date("l", strtotime($date_en_cours)) == "Sunday") { echo "c"; } else { echo "o"; } ?>"></td></tr>

<?php $day++; }?>

</table>

</td>

<td style="width:8px"></td>

<?php } ?>

</tr></table>

</div>

<script type="text/javascript"><!--

	<?php $n = 0; while($reservations_ = fetch_array($reservations)) { $n++;

		$range_start = strtotime(date("Y-m-d", strtotime($reservations_["book_start"])));
		$range_end = strtotime(date("Y-m-d", strtotime($reservations_["book_end"])));

		// Bug fix #2 - 21/11/2005
		if(date("Y",strtotime($reservations_["book_start"])) < $year) {
			$day = "01";
			$month = "01";
			$year_ = $year;
		} else {
			$day = date("d", strtotime($reservations_["book_start"]));
			$month = date("m", strtotime($reservations_["book_start"]));
			$year_ = date("Y", strtotime($reservations_["book_start"]));
		}

		$stamp = strtotime($year_ . "-" . $month . "-" . $day);

		while($stamp <= $range_end) {

			$day_name = Translate(date("l", $stamp), 1);

			if(date("l", $stamp) == "Saturday" || date("l", $stamp) == "Sunday") {
				$couleur = "#cf1f1f";
				$ferie = 0;
			} else {
				$couleur = "#ff3f3f";
				$ferie = 1;
			}

			echo "document.getElementById(\"j". $stamp . "\").style.background = \"#" . $validated_color . "\";" . chr(10);

			$day++;

			if(!checkdate($month, $day, $year_)) {
				$day = 1; $month ++;
				if($month > 12) { $month = 1; $year_++; }
			}

			$stamp = strtotime($year_ . "-" . $month . "-" . $day);
		}
	} ?>

	<?php if(isset($_REQUEST["stamp"])) { ?>
		ClickOnDay('<?php echo $_REQUEST["stamp"]; ?>'); // shows the day where a new booking has just been set
	<?php } else { ?>

		<?php if($year == date("Y")) { ?>
			ClickOnDay('<?php echo strtotime(date("Y-m-d")); ?>'); // shows today if the user asks for the current year's calendar
		<?php } else { ?>
			ClickOnDay('<?php echo strtotime($year . "-01-01"); ?>'); // shows the 1st of january if the user asks for another year
		<?php } ?>
	<?php } ?>

--></script>

<form action=""><input type="hidden" id="title_" name="title_" value="<?php echo $family_name . " / " . $object_name; ?>"></form>

</body>
</html>
