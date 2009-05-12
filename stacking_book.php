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

	$sql = "SELECT activity_start, activity_end, activity_step FROM rs_data_objects WHERE object_id = " . $_REQUEST["object_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

	//$temp_["activity_start"];
	//$temp_["activity_end"];
	$activity_step = $temp_["activity_step"];

	$booking_action = "New booking";
	$start_date = date($date_format, $_GET["stamp"]);
	$start_hour = date("H:i", $_GET["stamp"]);

	$array_duration = getDuration($activity_step * 60);

	$duration_days = $array_duration["days"];
	$duration_hours = $array_duration["hours"];
	$duration_minutes = $array_duration["minutes"];

	function getDuration($seconds) {

		$days = floor($seconds / 86400);

		$seconds = $seconds - ($days * 86400);
		$hours = floor($seconds / 3600);


		$seconds = $seconds - ($hours * 3600);
		$minutes = floor($seconds / 60);

		return array("days"=>$days, "hours"=>$hours, "minutes"=>$minutes);
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

	<title><?php echo $app_title; ?></title>

	<link rel="stylesheet" type="text/css" href="styles.php">

	<style type="text/css">
		.date { text-align:center; width:100px }
		.duration { text-align:center; width:50px }
		#iframe_action { visibility:visible; width:500px; height:200px }
	</style>

	<script type="text/javascript"><!--

		function $(id) { return document.getElementById(id); }

		function showFirstAvailability() {
			$("iframe_action").src = "actions.php?action_=show_first_availability&object_id=<?php echo $_REQUEST["object_id"]; ?>&start_date=" + $("start_date").value + "&start_hour=" + $("start_hour").value + "&duration=" + $("duration_days").value + "|" + $("duration_hours").value + "|" + $("duration_minutes").value;
		}

	--></script>

</head>

<body style="text-align:left; margin:10px">

	<div class="global" style="width:300px;top:50px">

	<span class="big_text"><?php echo Translate($booking_action, 1); ?></span>
	<br>

	<center>

	<div class="colorframe" style="padding:10px">

	<table class="table3">

		<tr>
			<td colspan="3" style="font-weight:bold">Start</td>
		</tr><tr>
			<td colspan="2">Date<br><input class="date" type="text" id="start_date" name="start_date" value="<?php echo $start_date; ?>"></td>
			<td>Hour<br><input class="duration" type="text" id="start_hour" name="start_hour" value="<?php echo $start_hour; ?>"></td>

		</tr><tr>
			<td colspan="3" style="font-weight:bold; padding-top:20px">Duration</td>
		<tr></tr>
			<td>Days<br><input class="duration" type="text" id="duration_days" name="duration_days" value="<?php echo $duration_days; ?>"></td>
			<td>Hours<br><input class="duration" type="text" id="duration_hours" name="duration_hours" value="<?php echo $duration_hours; ?>"></td>
			<td>Minutes<br><input class="duration" type="text" id="duration_minutes" name="duration_minutes" value="<?php echo $duration_minutes; ?>"></td>
		</tr><tr>
			<td colspan="3" style="font-weight:bold; padding-top:20px">First availability</td>
		</tr><tr>
			<td colspan="3" id="slot_display" style="text-align:center"></td>
		</tr>
	</table>

	</div>

	<br>

	<button type="button" onClick="showFirstAvailability()">Show first availability</button>

	</center></div>

	<iframe id="iframe_action"></iframe>

</body>

</html>
