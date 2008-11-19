<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	index.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	// application_access_level : 1 = anyone, 2 = guests, 3 = users
	// self_registration_mode : no_validation, email_validation, admin_validation, no_self_registration

	$self_registration_mode = param_extract("self_registration_mode");

	// Account creation errors
	if(isset($_POST["error_message"]) && $_POST["error_message"] != "") { $error_message = $_POST["error_message"]; } else { $error_message = ""; }

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title; ?></title>

<meta name="author" content="Jérôme ROGER">
<meta name="description" content="OpenBookings.org - Universal, multi-purpose online booking system">
<meta name="keywords" content="booking,bookings,reservation,reservations,resources,ressources,shared,partage,manage,management,gestion,planning,plannings,timetable,timetables,meeting,calendar,calendars,rooms,cars,free,download,open-source,web,html,php,mysql,apache,iis,odbc,online,intranet,internet,system,software">
<meta name="robots" content="index,nofollow">

<link rel="stylesheet" type="text/css" href="styles.php">

<?php if(isset($_COOKIE["bookings_user_id"])) { // cookie is set : user is already logged ?>

<frameset cols="210,*">
	<frame name="left_frame" src="menu.php">
	<frame name="middle_frame" src="intro.php">
</frameset>

<?php } else { // no cookie : shows login form ?>

	<script type="text/javascript"><!--

	<?php if($application_access_level == "1") { echo "function AnonymousAccess() { document.location = \"index.php?rand=" . rand(1,9999) . "&action_=login&user=anonymous&password=anonymous\"; }\n"; } ?>

		var current_tab_id = "";

		function $(id) { return document.getElementById(id); }

		function highLightTab(id, e) {

			if(id != current_tab_id) {

				switch(e.type) {
					case "mouseover": background_color = "#f7f7f7"; break;
					case "mouseout":  background_color = "#dfdfdf"; break;
				}

				$(id).style.background = background_color;
			}
		}

		function changeTab(id) {

			if(current_tab_id != "") { $(current_tab_id).style.background = "#dfdfdf"; }

			$(id).style.background = "#f7f7f7";

			switch(id) {

				case "connect":
				$("td_verify_password").innerHTML = "";
				$("td_first_name").innerHTML = "";
				$("td_last_name").innerHTML = "";
				$("td_email").innerHTML = "";
				$("submit_button").innerHTML = "<?php echo Translate("Connect", 1); ?>";
				break;

				case "register":
				$("td_verify_password").innerHTML = "<?php echo Translate("Verify password", 1); ?><br><input type=\"password\" id=\"verify_password\" name=\"verify_password\">";
				$("td_first_name").innerHTML = "<?php echo Translate("First name", 1); ?><br><input type=\"text\" id=\"first_name\" name=\"first_name\">";
				$("td_last_name").innerHTML = "<?php echo Translate("Last name", 1); ?><br><input type=\"text\" id=\"last_name\" name=\"last_name\">";
				$("td_email").innerHTML = "<?php echo Translate("Email", 1); ?><br><input type=\"text\" id=\"email\" name=\"email\">";
				$("submit_button").innerHTML = "<?php echo Translate("Register", 1); ?>";
				break;
			}

			$("action_").value = id; current_tab_id = id;
		}

		function connect(action) {
			$("login_form").submit();
		}

	--></script>

</head>

<body style="text-align:center">

<center>

<form id="login_form" name="login_form" method="post" action="actions.php" target="iframe_action">

<div class="global" style="width:300px;top:50px">

	<div class="tab_set" style="width:300px">
		<?php if($self_registration_mode != "no_self_registration") { ?><div class="tab" id="register" onMouseOver="highLightTab(this.id, event)" onMouseOut="highLightTab(this.id, event)" onClick="changeTab(this.id)">Register</div><?php } ?>
		<div class="tab" id="connect" onMouseOver="highLightTab(this.id, event)" onMouseOut="highLightTab(this.id, event)" onClick="changeTab(this.id)">Connect</div>
	</div>

	<div class="colorframe" style="width:260px">

		<center>

		<table class="table3" summary="">
			<tr><td id="td_username"><?php echo Translate("Username", 1); ?><br><input type="text" id="username" name="username"></td></tr>
			<tr><td id="td_password"><?php echo Translate("Password", 1); ?><br><input type="password" id="password" name="password"></td></tr>
			<tr><td id="td_verify_password"></td></tr>
			<tr><td id="td_first_name"></td></tr>
			<tr><td id="td_last_name"></td></tr>
			<tr><td id="td_email"></td></tr>
			<tr><td style="height:10px"></td></tr>
			<tr><td><center><button id="submit_button" type="button" style="width:150px" onClick="connect()"><?php echo Translate("Connect", 1); ?></button></center></td></tr>
		</table>

		</center>

	</div>

</div>

	<table>
		<tr><td colspan="3"style="height:20px"></td></tr>
		<tr>
			<td><a href="http://www.php.net" target="_blank"><img src="pictures/php_logo.gif" alt="This software was written in PHP !" height="36" width="69"></a></td>
			<td style="width:10px">
			<td><a href="http://www.w3.org" target="_blank"><img src="pictures/valid_html401.png" alt="Valid HTML 4.01!" height="31" width="88"></a></td>
			<td style="width:10px">
			<td><a href="http://www.mozilla.org/products/firefox/" target="_blank"><img src="pictures/get_firefox.gif" alt="Get FireFox!" height="32" width="110"></a></td>
		</tr>
	</table>

	<?php if($error_message != "") { echo "<div style=\"top:" . (360 + $height_offset) . "px; width:" . (304 + $width_offset) . "px; color:#ff0000; text-align:center\">" . $error_message . "</div>\n"; } // shows error message at the bottom of the form if any ?>

<input type="hidden" id="action_" name="action_" value="">

</form>

</center>

<iframe id="iframe_action" name="iframe_action"></iframe>

<script type="text/javascript">
	changeTab("connect");
</script>

<?php } ?>

</body>

</html>