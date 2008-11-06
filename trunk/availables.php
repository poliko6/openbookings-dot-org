<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	availables.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	$start = DateAndHour(DateReformat($_REQUEST["search_start_date"]), $_REQUEST["search_start_hour"] + $time_offset);
	$end = DateAndHour(DateReformat($_REQUEST["search_end_date"]), $_REQUEST["search_end_hour"] + $time_offset);


	echo DateAndHour(DateReformat($_REQUEST["search_start_date"]), $_REQUEST["search_start_hour"]);

	$start_ = date("Y-m-d H:i", strtotime($start));
	$end_ = date("Y-m-d H:i", strtotime($end));

	// extracts family name using family_id as parameter
	$sql = "SELECT family_name FROM rs_param_families WHERE family_id = " . $_REQUEST["family_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp); $family_name = $temp_["family_name"];

	// lists objects which are booked within the specified time range
	$sql  = "SELECT DISTINCT rs_data_objects.object_id ";
	$sql .= "FROM rs_data_bookings INNER JOIN rs_data_objects ON rs_data_bookings.object_id = rs_data_objects.object_id ";
	$sql .= "WHERE rs_data_objects.family_id = " . $_REQUEST["family_id"] . " ";

	$sql .= "AND ((rs_data_bookings.book_end > '" . $start . "' ";
	$sql .= "AND rs_data_bookings.book_end <= '" . $end . "') ";
	$sql .= "OR (rs_data_bookings.book_start < '" . $end . "' ";
	$sql .= "AND rs_data_bookings.book_start >= '" . $start . "') ";
	$sql .= "OR (rs_data_bookings.book_start <= '" . $start . "' ";
	$sql .= "AND rs_data_bookings.book_end >= '" . $end . "')) ";

	$unavailable_objets = db_query($database_name, $sql, "no", "no");

	$unavailable_list = "";

	while($unavailable_objets_ = fetch_array($unavailable_objets)) { $unavailable_list .= $unavailable_objets_["object_id"] . ","; }
	if($unavailable_list != "") { $unavailable_list = substr($unavailable_list, 0, strlen($unavailable_list)-1); } //shorts last comma

	// extracts disallowed objects according to current user profile and permissions
	$disallowed_objects_list = "";
	$sql = "SELECT DISTINCT object_id FROM rs_data_permissions ";
	$sql .= "WHERE permission = 'none' AND (user_id = " . $_COOKIE["bookings_user_id"] . " OR profile_id >= " . $_COOKIE["bookings_profile_id"] . ");";
	$temp = db_query($database_name, $sql, "no", "no");
	while($temp_ = fetch_array($temp)) { $disallowed_objects_list .= $temp_["object_id"] . ","; }

	if($disallowed_objects_list != "") { $disallowed_objects_list = substr($disallowed_objects_list ,0 ,-1); }

	// lists objects which are NOT booked in the specified time range
	$sql  = "SELECT DISTINCT object_id, object_name, manager_id ";
	$sql .= "FROM rs_data_objects ";
	$sql .= "WHERE rs_data_objects.family_id = " . $_REQUEST["family_id"] . " ";
	if($unavailable_list != "") { $sql .= "AND rs_data_objects.object_id NOT IN ( " . $unavailable_list . " )"; }
	if($disallowed_objects_list != "") { $sql .= "AND rs_data_objects.object_id NOT IN ( " . $disallowed_objects_list . " )"; }
	$sql .= ";";

	$available_objects = db_query($database_name, $sql, "no", "yes");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . Translate("Availables objects", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
		function OpenBooking(object_id) {
			window.open("book.php?book_id=0&object_id=" + object_id + "&start=<?php echo strtotime($start); ?>&end=<?php echo strtotime($end); ?>", "ajout_resa", "width=500, height=250, left=" + (screen.width-400)/2 + ", top=" + (screen.height-250)/2);
		}
--></script>

</head>

<body>

<span id="title_" style="font-size:24px"><?php echo $family_name; ?> <?php echo Translate("availables", 1); ?> <?php echo Translate("from", 1); ?> <?php echo date($date_format . " h:i", strtotime($start)); ?> <?php echo Translate("to", 1); ?> <?php echo date($date_format . " h:i", strtotime($end)); ?></span>

<table class="table2">

<tr>
	<th><?php echo Translate("Object name", 1); ?></th>
	<th><?php echo Translate("Person in charge", 1); ?></th>
	<th style="width:90px">&nbsp;</th>
</tr>




<?php while($available_objets_ = fetch_array($available_objects)) {

	if($available_objets_["manager_id"] != 0) {

		// gets the manager name using manager id as parameter
		$sql  = "SELECT last_name, first_name, email FROM rs_data_users ";
		$sql .= "WHERE user_id = " . $available_objets_["manager_id"] . ";";
		$manager = db_query($database_name, $sql, "no", "no"); $manager_ = fetch_array($manager);

		$manager_name = $manager_["first_name"] . " " . $manager_["last_name"];
		$manager_email = $manager_["email"];

	} else {

		$manager_name = Translate("None", 1);
		$manager_email = "";
	}

?><tr>

<td><?php echo $available_objets_["object_name"]; ?></td>
<td><?php if($manager_email != "") { ?><a href="mailto:<?php echo $manager_email; ?>"><?php } ?><?php echo $manager_name; ?><?php if($manager_email != "") { ?></a><?php } ?></td>
<td style="text-align:center"><button onClick="OpenBooking(<?php echo $available_objets_["object_id"]; ?>)"><?php echo Translate("Book it !", 1); ?></button></td>
</tr><?php } ?>

</table>

</body>

</html>
