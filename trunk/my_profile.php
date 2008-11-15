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

	$error_message = ""; $script = "";

	if(isset($_REQUEST["action_"])) {

		// prepares data to simplify database insert or update
		$first_name = addslashes($_REQUEST["first_name"]);
		$last_name = addslashes($_REQUEST["last_name"]);
		if($first_name == "") { $first_name = $last_name; }
		if($last_name == "") { $last_name = $first_name; }

		if($_REQUEST["remarks"] != "") { $remarks = "'" . addslashes($_REQUEST["remarks"]) . "'"; } else { $remarks = "NULL"; }

		switch($_REQUEST["action_"]) {

			case "update_my_profile":

			$sql = "UPDATE rs_data_users SET ";

			if($_REQUEST["password_confirm"] != "") {

				if($_REQUEST["password"] != $_REQUEST["password_confirm"]) {
					$error_message = "<span style=\"color:#ff0000\">" . Translate("Password was not changed as it was empty or did not meet password confirmation", 1) . ".</span>";
				} else {
					$sql .= "password = '" . $_REQUEST["password"] . "', ";
				}
			} //if

			$sql .= "first_name = '" . $first_name . "', ";
			$sql .= "last_name = '" . $last_name . "', ";
			$sql .= "email = '" . $_POST["email"] . "', ";
			$sql .= "language = '" . $_POST["language"] . "', ";
			$sql .= "date_format = '" . $_POST["date_format"] . "', ";
			$sql .= "user_timezone = " . $_POST["user_timezone"] . ", ";
			$sql .= "remarks = " . $remarks . " ";
			$sql .= "WHERE user_id = " . $_COOKIE["bookings_user_id"] . ";";
			db_query($database_name, $sql, "no", "no");

			$session_timeout = param_extract("session_timeout");

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

			$script .= "top.frames[0].location = \"menu.php\";\n";
			$script .= "top.frames[1].location = \"my_profile.php\";\n";

			break;

			case "delete_my_profile":

			$script = "top.location = \"index.php?action_=delog\";";

			$sql = "DELETE FROM rs_data_bookings WHERE user_id = " . $_COOKIE["bookings_user_id"] . ";";
			db_query($database_name, $sql, "no", "no");
			$sql = "DELETE FROM rs_data_permissions WHERE user_id = " . $_COOKIE["bookings_user_id"] . ";";
			db_query($database_name, $sql, "no", "no");
			$sql = "DELETE FROM rs_data_users WHERE user_id = " . $_COOKIE["bookings_user_id"] . ";";
			db_query($database_name, $sql, "no", "no");
			break;

		} // switch
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . $title; ?></title>

<script type="text/javascript"><!--
<?php echo $script; ?>
--></script>

</head>

<body></body>

</html>

<?php

	} else { // !isset($_REQUEST["action_"])


		$help["date_format"] = Translate("Use 'd' as day, 'm' as month, 'Y' as year with any char as separator", 1);

		$timezones_list  = "<option value=\"-43200\">(GMT-12) International Date Line West</option>";
		$timezones_list .= "<option value=\"-39600\">(GMT-11) Midway Island, Samoa</option>";
		$timezones_list .= "<option value=\"-36000\">(GMT-10) Hawaii</option>";
		$timezones_list .= "<option value=\"-32400\">(GMT-9) Alaska</option>";
		$timezones_list .= "<option value=\"-28800\">(GMT-8) Pacific Time (US & Canada), Tijuana</option>";
		$timezones_list .= "<option value=\"-25200\">(GMT-7) Mountain Time (US & Canada), Arizona</option>";
		$timezones_list .= "<option value=\"-25200\">(GMT-7) Chihuahua, La Paz, Mazatlan</option>";
		$timezones_list .= "<option value=\"-21600\">(GMT-6) Central Time (US & Canada), Guadalajara</option>";
		$timezones_list .= "<option value=\"-21600\">(GMT-6) Mexico City, Monterrey, Saskatchewan</option>";
		$timezones_list .= "<option value=\"-18000\">(GMT-5) Eastern Time (US & Canada), Bogota</option>";
		$timezones_list .= "<option value=\"-18000\">(GMT-5) Indiana (East), Lima, Quito</option>";
		$timezones_list .= "<option value=\"-14400\">(GMT-4) Atlantic Time (Canada), Caracas, La Paz, Santiago</option>";
		$timezones_list .= "<option value=\"-12600\">(GMT-3.5) Newfoundland</option>";
		$timezones_list .= "<option value=\"-10800\">(GMT-3) Brasilia, Buenos Aires, Georgetown, Greenland</option>";
		$timezones_list .= "<option value=\"-7200\">(GMT-2) Mid-Atlantic</option>";
		$timezones_list .= "<option value=\"-3600\">(GMT-1) Azores, Cape Verdi Island</option>";
		$timezones_list .= "<option value=\"0\">(GMT) Casablanca, Dublin, Edinburgh, Lisbon, London, Monrovia</option>";
		$timezones_list .= "<option value=\"3600\">(GMT+1) Amsterdam, Berlin, Belgrade, Bern, Bratislava</option>";
		$timezones_list .= "<option value=\"3600\">(GMT+1) Brussels, Budapest, Copenhagen, Ljubljana, Madrid</option>";
		$timezones_list .= "<option value=\"3600\">(GMT+1) Paris, Prague, Rome, Sarajevo, Skopje, Stockholm</option>";
		$timezones_list .= "<option value=\"3600\">(GMT+1) Vienna, Warsaw, West Central Africa, Zagreb</option>";
		$timezones_list .= "<option value=\"7200\">(GMT+2) Athens, Beirut, Bucharest, Cairo, Harare</option>";
		$timezones_list .= "<option value=\"7200\">(GMT+2) Helsinki, Istanbul, Jerusalem, Kyiv, Minsk</option>";
		$timezones_list .= "<option value=\"7200\">(GMT+2) Pretoria, Riga, Sofia, Tallinn, Vilnius</option>";
		$timezones_list .= "<option value=\"10800\">(GMT+3) Baghdad, Kuwait, Moscow, Nairobi</option>";
		$timezones_list .= "<option value=\"10800\">(GMT+3) Riyadh, St.Petersburg, Volgograd</option>";
		$timezones_list .= "<option value=\"+12600\">(GMT+3.5) Tehran</option>";
		$timezones_list .= "<option value=\"+14400\">(GMT+3.5) Abu Dhabi, Baku, Muscat, Tbilisi, Yerevan</option>";
		$timezones_list .= "<option value=\"+16200\">(GMT+4.5) Kabul</option>";
		$timezones_list .= "<option value=\"+18000\">(GMT+5) Ekaterinburg, Islamabad, Karachi, Tashkent</option>";
		$timezones_list .= "<option value=\"+19800\">(GMT+5.5) Chennai, Kolkata, Mumbai, New Delhi</option>";
		$timezones_list .= "<option value=\"+20700\">(GMT+5.75) Kathmandu</option>";
		$timezones_list .= "<option value=\"+21600\">(GMT+6) Almaty, Astana, Dhaka, Novosibirsk, Sri Jayawardenepura</option>";
		$timezones_list .= "<option value=\"+23400\">(GMT+6.5) Rangoon</option>";
		$timezones_list .= "<option value=\"+25200\">(GMT+7) Bankok, Hanoi, Jakarta, Krasnoyarsk</option>";
		$timezones_list .= "<option value=\"+28800\">(GMT+8) Beijing, Chongqing, Hong Kong, Irkutsk, Kuala Lumpur</option>";
		$timezones_list .= "<option value=\"+28800\">(GMT+8) Perth, Tapei, Ulaan Bataar, Uramqi, Singpore</option>";
		$timezones_list .= "<option value=\"+32400\">(GMT+9) Osaka, Sapporo, Seoul, Tokyo, Yakutsk</option>";
		$timezones_list .= "<option value=\"+34200\">(GMT+9.5) Adelaide, Darwin</option>";
		$timezones_list .= "<option value=\"+36000\">(GMT+10) Brisbane, Canberra, Gaum, Hobart, Melbourne</option>";
		$timezones_list .= "<option value=\"+36000\">(GMT+10) Port Moresby, Sydney, Vladivostok</option>";
		$timezones_list .= "<option value=\"+39600\">(GMT+11) Magadan, New Caledonia, Solomon Islands</option>";
		$timezones_list .= "<option value=\"+43200\">(GMT+12) Auckland, Fiji, Kamchatka, Marshall Islands, Wellington</option>";
		$timezones_list .= "<option value=\"+46800\">(GMT+13) Nuku'alofa</option>";

		// gets user infos
		$sql  = "SELECT user_id, login, last_name, first_name, email, locked, profile_id, language, date_format, user_timezone, remarks ";
		$sql .= "FROM rs_data_users WHERE user_id = " . $_COOKIE["bookings_user_id"] . ";";
		$user = db_query($database_name, $sql, "no", "no"); $user_ = fetch_array($user);

		$title = Translate("User details", 1);

		$login = $user_["login"];
		$last_name = stripslashes($user_["last_name"]);
		$first_name = stripslashes($user_["first_name"]);
		$email = $user_["email"];
		$language = $user_["language"];
		if($user_["date_format"] != "") { $date_format = $user_["date_format"]; } else { $date_format = "d/m/Y"; }
		$user_timezone = $user_["user_timezone"];
		$remarks = stripslashes($user_["remarks"]);

		$languages_list = "";
		$sql = "SHOW COLUMNS FROM rs_param_lang";
		$columns = db_query($database_name, $sql, "no", "no");

		while($columns_ = fetch_array($columns)) {

			if($columns_["Field"] != "lang_id") {

				if($columns_["Field"] == $language) {
					$languages_list .= "<option selected>";
				} else {
					$languages_list .= "<option>";
				}

				$languages_list .= $columns_["Field"] . "</option>";
			}
		}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . $title; ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
	function UpdateUser() {
		if( document.getElementById("password_confirm").value != "" && ( document.getElementById("password").value != document.getElementById("password_confirm").value )) {
			alert("<?php echo Translate("Password does not meet confirmation", 0); ?>");
		} else {
			document.getElementById("form_profile").submit();
		}
	}

	function CheckUser() {
		if(document.getElementById("first_name").value == "" || document.getElementById("last_name").value == "") {
			alert("<?php echo Translate("The values for the fields [First name] and [Last name] are required !", 0); ?>");
		} else {
			UpdateUser();
		}
	}

	function DeleteUser() {
		if(window.confirm("<?php echo Translate("WARNING ! Deleting your profile will also delete all your bookings", 0); ?>\n<?php echo Translate("Do you really want to delete your profile ?", 0); ?>")) {
			document.location = "my_profile.php?action_=delete_profile";
		}
	}

//--></script>

</head>

<body>

<center>

<form id="form_profile" name="form_profile" method="post" action="my_profile.php">

<table><tr><td><span style="font-size:24px"><?php echo $title; ?></span></td></tr>

<tr><td>

	<table class="table2" style="width:600px"><tr><td style="text-align:center">

		<center>

		<table class="table3">

			<tr>
			<td><?php echo Translate("Login", 1); ?><br><input type="text" id="login" name="login" style="width:160px" value="<?php echo $login; ?>" disabled></td>
			<td style="width:10px"></td>
			<td><?php echo Translate("Password", 1); ?><br><input type="password" id="password" name="password" style="width:160px" value=""></td>
			<td style="width:10px"></td>
			<td><?php echo Translate("Password confirm", 1); ?><br><input type="password" id="password_confirm" name="password_confirm" style="width:160px" value=""></td>
			</tr><tr>
			<td><?php echo Translate("Last name", 1); ?><br><input type="text" id="last_name" name="last_name" style="width:160px" value="<?php echo $last_name; ?>"></td>
			<td style="width:10px"></td>
			<td><?php echo Translate("First name", 1); ?><br><input type="text" id="first_name" name="first_name" style="width:160px" value="<?php echo $first_name; ?>"></td>
			<td style="width:10px"></td>
			<td><?php echo Translate("Email", 1); ?><br><input type="text" id="email" name="email" style="width:160px" value="<?php echo $email; ?>"></td>
			</tr>

			<tr><td colspan="5">

				<table><tr><td>
					<?php echo Translate("Language", 1); ?><br><select id="language" name="language"><?php echo $languages_list; ?></select>
				</td><td>

				<?php echo Translate("Date format", 1); ?><br><input id="date_format" name="date_format" style="width:70px; text-align:center" value="<?php echo $date_format; ?>" title="<?php echo $help["date_format"] ?>">&nbsp;( <?php echo Translate("Example", 1); ?> : <?php echo $date_format . " = " . date($date_format); ?> )

				</td></tr></table>

			</td></tr>

			<tr><td colspan="5"><?php echo Translate("User timezone", 1); ?><br><select id="user_timezone" name="user_timezone" style="width:520px"><?php echo $timezones_list; ?></select></td></tr>
			<tr><td colspan="5" style="height:10px"></td></tr>
			<tr><td colspan="5"><input type="text" id="remarks" name="remarks" style="width:520px; height:50px" value="<?php echo $remarks; ?>"></td></tr>
			<tr><td colspan="5" style="height:10px"></td></tr>

		</td></tr></table>

		</center>

	</td></tr></table>

</td></tr>

<tr><td style="height:10px"></td></tr>

<tr><td style="text-align:center">

	<center>

	<input type="hidden" id="action_" name="action_" value="update_my_profile">

	<table class="table3"><tr>
	<td><button type="button" onClick="CheckUser()"<?php if(!isset($_COOKIE["bookings_profile_id"]) || intval($_COOKIE["bookings_profile_id"]) < 2) { echo " disabled"; } ?>><?php echo Translate("Update", 1); ?></button></td>
	<td style="width:20px"></td>
	<td><button type="button" onClick="DeleteUser()"<?php if(!isset($_COOKIE["bookings_profile_id"]) || intval($_COOKIE["bookings_profile_id"]) < 2) { echo " disabled"; } ?>><?php echo Translate("Delete", 1); ?></button></td>
	</tr></table>

	</center>

</td></tr></table>

</form>

</center>

</body>

<script type="text/javascript"><!--
	document.getElementById("user_timezone").value = "<?php echo $user_timezone; ?>";
--></script>

</html>

<?php } // !isset($_REQUEST["action_"]) ?>