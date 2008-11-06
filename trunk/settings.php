<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	settings.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	if(isset($_POST["action_"])) {

		if(isset($_POST["users_can_customize_date"])) { $users_can_customize_date = "yes"; } else { $users_can_customize_date = "no"; }

		$sql = "UPDATE rs_param SET param_value = '" . $_POST["server_timezone"] . "' WHERE param_name = 'server_timezone';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["default_user_timezone"] . "' WHERE param_name = 'default_user_timezone';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["application_access_level"] . "' WHERE param_name = 'application_access_level';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["self_registration_mode"] . "' WHERE param_name = 'self_registration_mode';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["admin_email"] . "' WHERE param_name = 'admin_email';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["default_date_format"] . "' WHERE param_name = 'default_date_format';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $users_can_customize_date . "' WHERE param_name = 'users_can_customize_date';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["logo_file"] . "' WHERE param_name = 'logo_file';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["welcome_message"] . "' WHERE param_name = 'welcome_message';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["app_title"] . "' WHERE param_name = 'app_title';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["background_color"] . "' WHERE param_name = 'background_color';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["free_color"] . "' WHERE param_name = 'free_color';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["validated_color"] . "' WHERE param_name = 'validated_color';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["unvalidated_color"] . "' WHERE param_name = 'unvalidated_color';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["language"] . "' WHERE param_name = 'language';"; db_query($database_name, $sql, "no", "no");
		$sql = "UPDATE rs_param SET param_value = '" . $_POST["session_timeout"] . "' WHERE param_name = 'session_timeout';"; db_query($database_name, $sql, "no", "no");

		if(isset($_POST["reset_languages"])) { $sql = "UPDATE rs_data_users SET language = '" . $_POST["language"] . "';"; db_query($database_name, $sql, "no", "no"); }
		if(isset($_POST["reset_date_format"])) { $sql = "UPDATE rs_data_users SET date_format = '" . $_POST["default_date_format"] . "';"; db_query($database_name, $sql, "no", "no"); }
	}

	// constructs the languages list. if you want to localize the program to another language,
	// just add a column to the table rs_param_lang and fill it with translated word or sentences

	$languages_list = "";
	$sql = "SHOW COLUMNS FROM rs_param_lang";
	$columns = db_query($database_name, $sql, "no", "no");

	$language = param_extract("language");

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

	$server_timezone = param_extract("server_timezone");
	$default_user_timezone = param_extract("default_user_timezone");
	$application_access_level = param_extract("application_access_level");
	$self_registration_mode = param_extract("self_registration_mode");
	$admin_email = param_extract("admin_email");
	$default_date_format = param_extract("default_date_format");
	$users_can_customize_date = param_extract("users_can_customize_date");
	$logo_file = param_extract("logo_file");
	$welcome_message = param_extract("welcome_message");
	$app_title = param_extract("app_title");
	$background_color = param_extract("background_color");
	$free_color = param_extract("free_color");
	$validated_color = param_extract("validated_color");
	$unvalidated_color = param_extract("unvalidated_color");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . Translate("Application settings", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
	<?php if(isset($_POST["action_"])) { ?>parent.frames[0].location = "menu.php";<?php } ?>
--></script>

</head>

<body>

<form method="post" action="settings.php">

<center>

<table style="width:700px"><tr><td>

	<span style="font-size:24px"><?php echo Translate("Application settings", 1); ?></span><br>

	<table class="table2"><tr><td style="padding:10px">

		<table class="table3">
			<tr><td style="text-align:right"><?php echo Translate("Server timezone", 1); ?> :</td><td colspan="2"><select id="server_timezone" name="server_timezone" style="font-size:12px"><?php echo $timezones_list; ?></select></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Default user timezone", 1); ?> :</td><td colspan="2"><select id="default_user_timezone" name="default_user_timezone" style="font-size:12px"><?php echo $timezones_list; ?></select></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Default language", 1); ?> :</td><td><select id="language" name="language"><?php echo $languages_list; ?></select></td><td style="font-size:12px"><input type="checkbox" id="reset_languages" name="reset_languages"><?php echo Translate("Replace in all users profiles", 1); ?></td></tr>
			<tr><td colspan="3" style="height:10px"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Default date format", 1); ?> :</td><td><input id="default_date_format" name="default_date_format" style="width:70px; text-align:center" value="<?php echo $default_date_format; ?>"></td><td><table class="table4"><tr><td><input type="checkbox" id="users_can_customize_date" name="users_can_customize_date"<?php if($users_can_customize_date == "yes") { echo " checked"; } ?>></td><td><?php echo Translate("Users can customize date format", 1); ?></td></tr><tr><td><input type="checkbox" id="reset_date_format" name="reset_date_format"></td><td><?php echo Translate("Replace in all users profiles", 1); ?></td></tr></table></td></tr>
			<tr><td colspan="3" style="height:10px"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Logo file", 1); ?> : [<?php echo Translate("app root folder", 1); ?>]/pictures /</td><td colspan="2"><input id="logo_file" name="logo_file" style="width:120px" value="<?php echo $logo_file; ?>"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Application title", 1); ?> :</td><td colspan="2"><input id="app_title" name="app_title" style="width:280px" value="<?php echo $app_title; ?>"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Welcome message", 1); ?> :</td><td colspan="2"><input id="welcome_message" name="welcome_message" style="width:280px" value="<?php echo $welcome_message; ?>"></td></tr>
			<tr><td colspan="3" style="height:10px"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Background color", 1); ?> :</td><td>#<input id="background_color" name="background_color" style="width:60px" value="<?php echo $background_color; ?>"></td><td><table><tr><td style="border:1px solid;height:20px;padding:0px;width:30px; background:#<?php echo $background_color; ?>"></td></tr></table></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("No booking color", 1); ?> :</td><td>#<input id="free_color" name="free_color" style="width:60px" value="<?php echo $free_color; ?>"></td><td><table><tr><td style="border:1px solid;height:20px;padding:0px;width:30px; background:#<?php echo $free_color; ?>"></td></tr></table></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Validated bookings color", 1); ?> :</td><td>#<input id="validated_color" name="validated_color" style="width:60px" value="<?php echo $validated_color; ?>"></td><td><table><tr><td style="border:1px solid;height:20px;padding:0px;width:30px; background:#<?php echo $validated_color; ?>"></td></tr></table></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Unvalidated bookings color", 1); ?> :</td><td>#<input id="unvalidated_color" name="unvalidated_color" style="width:60px" value="<?php echo $unvalidated_color; ?>"></td><td><table><tr><td style="border:1px solid; height:20px;padding:0px;width:30px; background:#<?php echo $unvalidated_color; ?>"></td></tr></table></td></tr>
			<tr><td colspan="3" style="height:10px"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Application access level", 1); ?> :</td><td colspan="2"><select id="application_access_level" name="application_access_level" onChange="DisableValidationMode()"><option value="1"<?php if($application_access_level == "1") { echo " selected"; } ?>><?php echo Translate("Anyone", 1); ?></option><option value="2"<?php if($application_access_level == "2") { echo " selected"; } ?>><?php echo Translate("Guest", 1); ?></option><option value="3"<?php if($application_access_level == "3") { echo " selected"; } ?>><?php echo Translate("User", 1); ?></option></select></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Self-registration method", 1); ?> :</td><td colspan="2"><select id="self_registration_mode" name="self_registration_mode" style="width:350px"><option value="no_validation"><?php echo Translate("Automatic (no validation required)", 1); ?></option><option value="email_validation"><?php echo Translate("New user must reply to an email", 1); ?></option><option value="admin_validation"><?php echo Translate("By an administrator", 1); ?></option><option value="no_self_registration"><?php echo Translate("No self-registration (account creation is restricted to admins)", 1); ?></option></select></td></tr>
			<tr><td colspan="3" style="height:10px"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Administrator email", 1); ?> :</td><td colspan="2"><input id="admin_email" name="admin_email" style="width:280px" value="<?php echo $admin_email; ?>"></td></tr>
			<tr><td colspan="3" style="height:10px"></td></tr>
			<tr><td style="text-align:right"><?php echo Translate("Session timeout", 1); ?> :</td><td><input id="session_timeout" name="session_timeout" style="width:70px" value="<?php echo param_extract("session_timeout"); ?>"></td><td><?php echo Translate("seconds", 1); ?> ( 0 = <?php echo Translate("Never", 1); ?> )</td></tr>
		</table>

	</td></tr></table>

</td></tr>

<tr><td style="height:10px"></td></tr>

<tr><td style="text-align:center"><button type="submit"<?php if(!isset($_COOKIE["bookings_profile_id"]) || intval($_COOKIE["bookings_profile_id"]) < 2) { echo " disabled"; } ?>><?php echo Translate("Save changes", 1); ?></button></td></tr>

</table>

<input type="hidden" id="action_" name="action_" value="save_settings">

</form>

</center>

<script type="text/javascript"><!--
	document.getElementById("server_timezone").value = "<?php echo $server_timezone; ?>";
	document.getElementById("default_user_timezone").value = "<?php echo $default_user_timezone; ?>";
	document.getElementById("self_registration_mode").value = "<?php echo $self_registration_mode; ?>";
--></script>

</body>

</html>
