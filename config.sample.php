<?php
	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	config.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	//path to the bookings application folders (used for email validation system)
	$app_url = "http://localhost/openbookings/";

	// Database Settings

	// Name or IP adress of the mysql server (default : "localhost" if both database and web server are installed on the same machine)
	$db_server_address = "localhost";

	// Name of the MySQL database or ODBC source (default : "openbookings")
	$database_name = "openbookings";

	// Store once your database connection credentials here
	$db_user = "root";
	$db_password = "";

	// Choose your connection mode ("mysql" or "odbc")
	$db_connection_type = "mysql"; // "odbc";
?>
