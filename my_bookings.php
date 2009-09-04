<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	my_bookings.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	// extracts current user name
	$sql = "SELECT first_name, last_name ";
	$sql .= "FROM rs_data_users ";
	$sql .= "WHERE user_id = " . $_REQUEST["user_id"] . ";";
	$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
	$user_name = $temp_["first_name"] . " " . $temp_["last_name"];

	// extracts bookings list
	$sql  = "SELECT rs_data_objects.object_name, rs_param_families.family_name, rs_data_bookings.book_date, rs_data_bookings.book_start, rs_data_bookings.book_end, rs_data_bookings.misc_info, rs_data_bookings.validated ";
	$sql .= "FROM (rs_data_bookings INNER JOIN rs_data_objects ON rs_data_bookings.object_id = rs_data_objects.object_id) INNER JOIN rs_param_families ON rs_data_objects.family_id = rs_param_families.family_id ";
	$sql .= "WHERE rs_data_bookings.user_id = " . $_REQUEST["user_id"] . " ";
	$sql .= "AND rs_data_bookings.book_end >= '" . date("Y-m-d") . "' ";
	$sql .= "ORDER BY rs_data_bookings.book_start;";
	$bookings = db_query($database_name, $sql, "no", "no");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title><?php echo Translate("My bookings", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
--></script>

</head>

<body style="margin:10px; text-align:left">

<span class="big_text"><?php echo Translate("All bookings from", 1) ?> <?php echo $user_name; ?></span><br>

<table class="table2" summary="">

	<tr>
		<th><?php echo Translate("Family", 1); ?></th>
		<th><?php echo Translate("Object", 1); ?></th>
		<th><?php echo Translate("Booking date", 1); ?></th>
		<th><?php echo Translate("Start", 1); ?></th>
		<th><?php echo Translate("End", 1); ?></th>
		<th><?php echo Translate("Remarks", 1); ?></th>
	</tr>

<?php while($bookings_ = fetch_array($bookings)) { ?><tr>
<td><?php echo $bookings_["family_name"]; ?></td>
<td><?php echo $bookings_["object_name"]; ?></td>
<td style="text-align:center"><?php echo date($date_format, strtotime($bookings_["book_date"])); ?></td>
<td style="text-align:center"><?php echo date($date_format . " H:i:s", strtotime($bookings_["book_start"])); ?></td>
<td style="text-align:center"><?php echo date($date_format . " H:i:s", strtotime($bookings_["book_end"])); ?></td>
<td style="text-align:left"><?php echo stripslashes($bookings_["misc_info"]); ?></td>
</tr><?php } ?>

</table>

</body>

</html>