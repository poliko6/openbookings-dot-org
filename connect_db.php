<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	connect_db.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	// Database connection mode abstraction (3 functions)

	// Connects to database according the selected connection type
	function db_query($db_name, $sql, $bypass_admin_security, $debug_mode) {

		$sql = str_replace("#", "", $sql); // basic protection against sql injections

		// $bypass_admin_security = "yes" when an user is self-registering.
		// This is the only case where a non-identified user can modify the database
		// $debug_mode = "yes" shows the sql statement

		if($debug_mode == "yes") { echo "<hr>-- Debug info--<br>" .$sql . "<hr>"; }

		global $db_connection_type, $db_server_address, $db_user, $db_password;
		$result = false;

		switch($db_connection_type) {

			case "odbc":
			$db_connection = odbc_connect($db_name, $db_user, $db_password);

			if(substr($sql, 0, 6) == "SELECT" || substr($sql, 0, 12) == "SHOW COLUMNS" || $bypass_admin_security == "yes") {
				$result = odbc_exec($db_connection, $sql);
			} else {
				if(isset($_COOKIE["bookings_profile_id"]) && intval($_COOKIE["bookings_profile_id"]) > 1) {
					odbc_exec($db_connection, $sql);
					$result = true;
				}
			}

			odbc_close($db_connection);

			break;

			case "mysql":

			$db_connection = mysql_connect($db_server_address, $db_user, $db_password);
			mysql_select_db($db_name, $db_connection);

			if(substr($sql, 0, 6) == "SELECT" || substr($sql, 0, 12) == "SHOW COLUMNS" || $bypass_admin_security == "yes") {
				$result = mysql_query($sql);
			} else {
					
				if(isset($_COOKIE["bookings_user_id"])) {
						
					$bookings_user_id = checkVar("sql", $_COOKIE["bookings_user_id"], "int", "", "", "0", "");
					
					$sql = "SELECT profile_id FROM rs_data_users WHERE user_id = " . $bookings_user_id . ";";
					$temp = mysql_query($sql);
					$profile_id = ($temp_ = mysql_fetch_array($temp))?$temp_["profile_id"]:0;
						
					$result = ($profile_id > 1)?mysql_query($sql):false;
				}
			}

			mysql_close($db_connection);
		}

		return $result;
	}

	function fetch_array($resource_array) {

		global $db_connection_type;

		switch($db_connection_type) {

			case "odbc":
			$result = odbc_fetch_array($resource_array);
			break;

			case "mysql":
			$result = mysql_fetch_array($resource_array);
		}

		return $result;
	}

	function num_rows($resource_array) { // calcule le nombre de lignes retournées par une requête (couche d'abstraction pour mysql_num_rows et odbc_num_rows)

		global $db_connection_type;

		switch($db_connection_type) {

			case "odbc":
			$result = odbc_num_rows($resource_array);
			break;

			case "mysql":
			$result = mysql_num_rows($resource_array);
		}

		return $result;
	}
?>
