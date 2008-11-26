<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	intro.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	$logo_file = param_extract("logo_file");
	$welcome_message = param_extract("welcome_message");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . Translate("Intro", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php" />

<script type="text/javascript"><!--
--></script>

</head>

<body style="text-align:center">

<center>

<table style="text-align:center" summary="">
<tr><td style="height:50px"></td></tr>
<tr><td><img src="pictures/<?php echo $logo_file; ?>" alt="Logo picture"></td></tr>
<tr><td style="height:20px"></td></tr>
<tr><td style="font-size:40"><?php echo $welcome_message; ?></td></tr>
</table>

</center>

</body>

</html>