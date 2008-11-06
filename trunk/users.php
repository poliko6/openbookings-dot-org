<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	users.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	$error_message = ""; $script = "";

	// update or delete are performed only if current user is an admin.
	// this prevents hacking attempt by passing values through the url
	if(isset($_REQUEST["action_"]) && $_COOKIE["bookings_profile_id"] == "4") {

		if($_REQUEST["action_"] != "delete_user") {
			// prepares data to simplify database insert or update
			$first_name = addslashes($_REQUEST["first_name"]);
			$last_name = addslashes($_REQUEST["last_name"]);
			if($first_name == "") { $first_name = $last_name; }
			if($last_name == "") { $last_name = $first_name; }

			if(isset($_REQUEST["locked"])) { $locked = 1; } else { $locked = 0; }

			if($_REQUEST["remarks"] != "") { $remarks = "'" . addslashes($_REQUEST["remarks"]) . "'"; } else { $remarks = "NULL"; }
		}

		switch($_REQUEST["action_"]) {

			case "insert_new_user":

			if($_REQUEST["password"] == "" || $_REQUEST["password"] != $_REQUEST["password_confirm"]) {
				$error_message = "<span style=\"color:#ff0000\">" . Translate("User was not added as password was empty or did not meet password confirmation", 1) . ".</span>";
			} else {

				$sql  = "INSERT INTO rs_data_users ( login, password, first_name, last_name, email, locked, profile_id, language, date_format, user_timezone, remarks ) VALUES ( ";
				$sql .= "'" . $_REQUEST["login"] . "', ";
				$sql .= "'" . $_REQUEST["password"] . "', ";
				$sql .= "'" . $first_name . "', "; // addslashes already done
				$sql .= "'" . $last_name . "', "; // addslashes already done
				$sql .= "'" . $_REQUEST["email"] . "', ";
				$sql .= "'" . $locked . "', ";
				$sql .= "'" . $_REQUEST["profile_id"] . "', ";
				$sql .= "'" . $_REQUEST["language"] . "', ";
				$sql .= "'" . $_REQUEST["date_format"] . "', ";
				$sql .= $_REQUEST["user_timezone"] . ", ";
				$sql .= $remarks . " );"; // addslashes already done

				db_query($database_name, $sql, "no", "no");
			}

			break;

			case "update_user":

			$sql = "UPDATE rs_data_users SET ";
			$sql .= "login = '" . $_REQUEST["login"] . "', ";

			if($_REQUEST["password_confirm"] != "") {

				if($_REQUEST["password"] != $_REQUEST["password_confirm"]) {
					$error_message = "<span style=\"color:#ff0000\">" . Translate("Password was not changed as it was empty or did not meet password confirmation", 1) . ".</span>";
				} else {
					$sql .= "password = '" . $_REQUEST["password"] . "', ";
				}
			} //if

			$sql .= "first_name = '" . $first_name . "', "; // addslashes already done
			$sql .= "last_name = '" . $last_name . "', "; // addslashes already done
			$sql .= "email = '" . $_REQUEST["email"] . "', ";
			$sql .= "locked = '" . $locked . "', ";
			$sql .= "profile_id = '" . $_REQUEST["profile_id"] . "', ";
			$sql .= "language = '" . $_REQUEST["language"] . "', ";
			$sql .= "date_format = '" . $_REQUEST["date_format"] . "', ";
			$sql .= "user_timezone = " . $_REQUEST["user_timezone"] . ", ";
			$sql .= "remarks = " . $remarks . " "; // addslashes already done
			$sql .= "WHERE user_id = " . $_REQUEST["user_id"] . ";";
			db_query($database_name, $sql, "no", "no");

			if($_COOKIE["bookings_profile_id"] == $_REQUEST["profile_id"]) { // logged user is modifying his own profile

				// update language, timezone and language cookies to reflect the changes immediately
				if($session_timeout != 0) {
					setcookie("bookings_language", $_REQUEST["language"], (time() + $session_timeout));
					setcookie("bookings_time_offset", (intval($_REQUEST["user_timezone"]) - param_extract("server_timezone")), (time() + $session_timeout));
					setcookie("bookings_date_format", $_REQUEST["date_format"], (time() + $session_timeout));
				} else {
					setcookie("bookings_language", $_REQUEST["language"]);
					setcookie("bookings_time_offset", (intval($_REQUEST["user_timezone"]) - param_extract("server_timezone")));
					setcookie("bookings_date_format", $_REQUEST["date_format"]);
				}
			}

			$script .= "top.frames[0].location = \"menu.php\";\n";
			$script .= "top.frames[1].location = \"users.php\";\n";

			break;

			case "delete_user":

			// deletes user's bookings
			$sql = "DELETE FROM rs_data_bookings WHERE user_id = " . $_REQUEST["user_id"] . ";";
			db_query($database_name, $sql, "no", "no");

			// deletes user's permissions
			$sql = "DELETE FROM rs_data_permissions WHERE user_id = " . $_REQUEST["user_id"] . ";";
			db_query($database_name, $sql, "no", "no");

			// delete user's profile
			$sql = "DELETE FROM rs_data_users WHERE user_id = " . $_REQUEST["user_id"] . ";";
			db_query($database_name, $sql, "no", "no");
			break;


		} //switch
	} //if

	$sql  = "SELECT user_id, login, last_name, first_name, email, user_timezone, locked, profile_name, language, remarks ";
	$sql .= "FROM rs_data_users INNER JOIN rs_param_profiles ON rs_data_users.profile_id = rs_param_profiles.profile_id ORDER BY last_name, first_name;";
	$users = db_query($database_name, $sql, "no", "no");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . Translate("Users list", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
	<?php echo $script; ?>
	function EditUser(user_id) { document.location = "user.php?user_id=" + user_id; }
--></script>

</head>

<body>

<?php if($error_message != "") { echo $error_message . "<p>"; } ?><p>

<span style="font-size:24px"><?php echo Translate("Users list", 1); ?></span>

<table class="table2">

<tr>
	<th><?php echo Translate("User name", 1);?></th>
	<th><?php echo Translate("Login", 1);?></th>
	<th><?php echo Translate("Profile", 1);?></th>
	<th><?php echo Translate("Email", 1);?></th>
	<th><?php echo Translate("Language", 1);?></th>
	<th><?php echo Translate("Timezone", 1);?></th>
	<th><?php echo Translate("Remarks", 1);?></th>
	<th><?php echo Translate("Locked", 1);?></th>
</tr>

<?php while($users_ = fetch_array($users)) { ?><tr>
<td><a href="JavaScript:EditUser(<?php echo $users_["user_id"]; ?>)"><?php echo $users_["first_name"];  if($users_["first_name"] != $users_["last_name"]) { echo " " . $users_["last_name"]; } ?></a></td>
<td style="text-align:center"><?php echo $users_["login"]; ?></td>
<td style="text-align:center"><?php echo Translate($users_["profile_name"], 1); ?></td>
<td><?php echo $users_["email"]; ?></td>
<td style="text-align:center"><?php echo Translate($users_["language"], 1); ?></td>
<td style="text-align:center">GMT<?php if($users_["user_timezone"] >= 0) { echo "+"; } ?><?php echo $users_["user_timezone"]/3600; ?></td>
<td><?php echo $users_["remarks"]; ?></td>
<td style="text-align:center"><?php if($users_["locked"]) { echo Translate("Yes", 1); } else { echo Translate("No", 1); } ?></td>
</tr><?php } ?>

</table>

<p style="text-align:center">

<button onClick="EditUser(0)"<?php if(!isset($_COOKIE["bookings_profile_id"]) || $_COOKIE["bookings_profile_id"] != "4") { echo " disabled"; } ?>><?php echo Translate("Add", 1); ?></button>

</body>

</html>
