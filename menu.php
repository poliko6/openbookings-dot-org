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

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<?php

	// selects current year if not specified by posted vars
	if(isset($_REQUEST["annee"])) { $annee = $_REQUEST["annee"]; } else { $annee = date("Y"); }

	if(isset($_REQUEST["family_id"])) { $family_id = $_REQUEST["family_id"]; }
	if(isset($_REQUEST["object_id"])) { $object_id = $_REQUEST["object_id"]; }

	// extracts disallowed objects according to current user profile and permissions
	$disallowed_objects_list = "";
	$sql = "SELECT DISTINCT object_id FROM rs_data_permissions ";
	$sql .= "WHERE permission = 'none' AND (user_id = " . $_COOKIE["bookings_user_id"] . " OR profile_id >= " . $_COOKIE["bookings_profile_id"] . ");";
	$temp = db_query($database_name, $sql, "no", "no");
	while($temp_ = fetch_array($temp)) { $disallowed_objects_list .= $temp_["object_id"] . ","; }

	if($disallowed_objects_list != "") { $disallowed_objects_list = substr($disallowed_objects_list, 0 ,-1); }

	if($_COOKIE["bookings_profile_id"] == "4") {
		$sql = "SELECT family_id, family_name, sort_order FROM rs_param_families ORDER BY sort_order;";
	} else {
		// avoids showing families without objects if current user is not an administator
		$sql = "SELECT DISTINCT rs_param_families.family_id, rs_param_families.family_name, rs_param_families.sort_order ";
		$sql .= "FROM rs_param_families INNER JOIN rs_data_objects ON rs_param_families.family_id = rs_data_objects.family_id ";
		if($disallowed_objects_list != "") { $sql .= "WHERE rs_data_objects.object_id NOT IN ( " . $disallowed_objects_list . " )"; }
		$sql .= ";";
	}

	$families = db_query($database_name, $sql, "no", "no");

	$n = 0; $families_list = "";

	// checks for objects without families
	$sql = "SELECT object_id FROM rs_data_objects WHERE family_id = 255;";
	$temp = db_query($database_name, $sql, "no", "no");

	while($families_ = fetch_array($families)) { $n++;

		if(!isset($family_id) && $n == 1) { $family_id = $families_["family_id"]; }

		$families_list .= "<option value=\"" . $families_["family_id"] . "\"";
		if($families_["family_id"] == $family_id) { $families_list .= " selected"; }
		$families_list .= ">" . $families_["family_name"] . "</option>";
	}

	// shows a temporary 'unclassified' family only if objects without families
	if($temp_ = fetch_array($temp)) {
		$families_list .= "<option value=\"255\"";
		if($family_id == 255) { $families_list .= " selected"; }
		$families_list .= ">&lt;" . Translate("Unclassified", 1) . "&gt;</option>\n";
	}

	// extracts allowed objects list for current user
	$sql  = "SELECT object_id, object_name FROM rs_data_objects ";
	$sql .= "WHERE family_id = " . $family_id . " ";
	if($disallowed_objects_list != "") { $sql .= "AND object_id NOT IN ( " . $disallowed_objects_list . " ) "; }
	$sql .= "ORDER BY object_name;";
	$objets = db_query($database_name, $sql, "no", "no");

	$n = 0; $objects_list = "";

	while($objets_ = fetch_array($objets)) { $n++;

		if(!isset($object_id) && $n == 1) { $object_id = $objets_["object_id"]; }

		$objects_list .= "<option value=\"" . $objets_["object_id"] . "\"";
		if($objets_["object_id"] == $object_id) { $objects_list .= " selected"; }
		$objects_list .= ">" . $objets_["object_name"] . "</option>\n";
	}

	// get user from database
	$sql = "SELECT CONCAT(first_name, ' ', last_name) AS user FROM rs_data_users WHERE user_id = " . $_COOKIE["bookings_user_id"] . ";";
	$user = db_query($database_name, $sql, "no", "no"); $user_ = fetch_array($user); $user = $user_["user"];
?>

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title><?php echo $app_title . " :: " . Translate("Menu", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--

	<?php includeCommonScripts(); ?>

	function ChangeFamily(FamilyId) {

		if(FamilyId !=0) {
			document.location = "menu.php?user_id=<?php echo $_COOKIE["bookings_user_id"]; ?>&family_id=" + FamilyId + "&annee=" + $("annee").value;
		} else {
			$("object_id").disabled = true;
			$("show_object").disabled = true;
			$("show_year").disabled = true;
			$("show_week").disabled = true;
		}
	}

	function ChangeObject(ObjectId) {

		if(ObjectId == 0) {
			$("family_id").disabled = true;
			$("show_family").disabled = true;
			$("show_year").disabled = true;
			$("show_week").disabled = true;
		} else {
			$("family_id").disabled = false;
			$("show_family").disabled = false;
			$("show_year").disabled = false;
			$("show_week").disabled = false;
		}
	}

	function ShowFamily() { top.frames[1].location = "family.php?user_id=<?php echo $_COOKIE["bookings_user_id"]; ?>&family_id=" + $("family_id").value; }
	function ShowObject() { top.frames[1].location = "object.php?object_id=" + $("object_id").value + "&user_id=<?php echo $_COOKIE["bookings_user_id"]; ?>&family_id=" + $("family_id").value; }
	function AvailabilitySearch() { $("search_form").submit(); }
	function ShowCalendar(filename) { $("form_resa").action = filename; $("form_resa").submit(); }
	function DeLog() { $("action_").value = "delog"; $("form_action").submit(); }

--></script>

</head>

<body style="text-align:center; margin:5px 0px 5px 5px">

<center>

<span class="small_text">
	<b><?php echo $user; ?></b>
	<br>
	<a href="my_profile.php" target="middle_frame"><?php if($_COOKIE["bookings_profile_id"] == "2" || $_COOKIE["bookings_profile_id"] == "3") { echo Translate("my profile", 1) . "&nbsp;|&nbsp;"; } ?></a><a href="JavaScript:DeLog()"><?php echo Translate("disconnect", 1); ?></a>
</span>

<hr>

<form id="form_resa" name="form_resa" method="post" action="calendar.php" target="middle_frame">

<table summary="">

<tr><td style="text-align:center">

	<table summary="">
	<tr><td style="font-weight:bold"><?php echo Translate("Year", 1); ?></td></tr>
	<tr><td><input type="text" id="annee" name="annee" style="width:60px; text-align:center" value="<?php echo $annee; ?>"></td></tr>
	</table>

</td></tr>

<tr><td style="font-weight:bold"><?php echo Translate("Family", 1); ?></td></tr>
<tr><td>

	<table summary=""><tr>
	<td><select id="family_id" name="family_id" style="width:155px" onChange="ChangeFamily(this.value)">
	<?php echo $families_list; if($_COOKIE["bookings_profile_id"] == "4") { echo "<option value=\"0\">" . Translate("Add", 1) . "...</option>"; } ?>
	</select></td>
	<?php if($_COOKIE["bookings_profile_id"] == "4") { ?><td><button id="show_family" type="button" style="width:16px; height:16px" onClick="ShowFamily()"></button></td><?php } ?>
	</tr></table>

</td></tr>

<tr><td style="font-weight:bold"><?php echo Translate("Object", 1); ?></td></tr>
<tr><td>

	<table summary=""><tr>
	<td><select id="object_id" name="object_id" style="width:155px">
	<?php echo $objects_list; if($_COOKIE["bookings_profile_id"] == "4") { echo "<option value=\"0\">" . Translate("Add", 1) . "...</option>"; } ?>
	</select></td>
	<?php if($_COOKIE["bookings_profile_id"] == "4") { ?><td><button id="show_object" type="button" style="width:16px; height:16px" onClick="ShowObject()"></button></td><?php } ?>
	</tr></table>

</td></tr>

</table>

<input type="hidden" id="user_id" name="user_id" value="<?php echo $_COOKIE["bookings_user_id"]; ?>">

<table summary="">
<tr><td style="height:20px"></td></tr>
<tr><td><button id="show_year" type="button" style="width:160px" onClick="ShowCalendar('calendar.php')"><?php echo Translate("Show year", 1); ?></button></td></tr>
<tr><td style="height:10px"></td></tr>
<tr><td style="text-align:center">- <?php echo Translate("or", 1); ?> -</td></tr>
<tr><td style="height:10px"></td></tr>

<tr><td>

<table class="table3" summary=""><tr>
	<td style="font-weight:bold"><?php echo Translate("Week", 1); ?></td>
	<td><input id="n_semaine" name="n_semaine"  style="width:24px; text-align:center" value="<?php echo date("W"); ?>"></td>
	<td></td>
	<td><button id="show_week" type="button" style="width:60px" onClick="ShowCalendar('week.php?year=' + $('annee').value + '&amp;week=' + $('n_semaine').value)"><?php echo Translate("Show", 1); ?></button></td>
</tr></table>

</td></tr>

<?php if(intval($_COOKIE["bookings_profile_id"]) > 1) { ?>
<tr><td style="height:10px"></td></tr>
<tr><td style="text-align:center">- <?php echo Translate("or", 1); ?> -</td></tr>
<tr><td style="height:10px"></td></tr>
<tr><td><button id="show_whole_family" type="button" style="width:160px" onClick="ShowCalendar('whole_family.php?family_id=' + $('family_id').value + '&amp;year=' + $('annee').value + '&amp;week=' + $('n_semaine').value)"><?php echo Translate("Whole family", 1); ?></button></td></tr>
<tr><td style="height:10px"></td></tr>
<tr><td><button id="show_my_bookings" type="button" style="width:160px" onClick="ShowCalendar('my_bookings.php')"><?php echo Translate("My Bookings", 1); ?></button></td></tr>
<?php } ?>

<tr><td style="height:10px"></td></tr>

</table>

<input type="hidden" id="screen_width" name="screen_width">
<input type="hidden" id="screen_height" name="screen_height">

</form>

<br><br>

<form id="search_form" name="search_form" method="post" action="availables.php" target="middle_frame">

<table summary="">

	<tr><td><span class="big_text"><?php echo Translate("Availabilities", 1); ?></span></td></tr>
	<tr><td style="height:12px"></td></tr>
	<tr><td style="font-weight:bold"><?php echo Translate("Family", 1); ?></td></tr>
	<tr><td><select id="family_id2" name="family_id" style="width:175px">
	<?php echo $families_list; ?></select></td>
	</tr>

	<tr><td style="text-align:center">

		<table summary=""><tr>
			<td colspan="2" style="font-weight:bold"><?php echo Translate("Start", 1); ?></td>
		</tr><tr>
			<td><input type="text" id="search_start_date" name="search_start_date" style="width:90px; text-align:center" value="<?php echo date($date_format); ?>"></td>
			<td style="width:10px"></td>
			<td><input id="search_start_hour" name="search_start_hour" value="08:00" style="width:50px; text-align:center"></td>
		</tr><tr>
			<td colspan="2" style="font-weight:bold"><?php echo Translate("End", 1); ?></td>
		</tr><tr>
			<td><input type="text" id="search_end_date" name="search_end_date" style="width:90px; text-align:center" value="<?php echo date($date_format); ?>"></td>
			<td style="width:10px"></td>
			<td><input id="search_end_hour" name="search_end_hour" value="18:00" style="width:50px; text-align:center"></td>
		</tr></table>

	</td></tr>

</table>

<table summary="">
	<tr><td style="height:20px"></td></tr>
	<tr><td><button type="button" style="width:160px" onClick="AvailabilitySearch()"><?php echo Translate("Search", 1); ?></button></td></tr>
	<tr><td style="height:15px"></td></tr>
</table>

<input type="hidden" id="user_id2" name="user_id" value="<?php echo $_COOKIE["bookings_user_id"]; ?>">

</form>

<br>

<?php if($_COOKIE["bookings_profile_id"] == "4") { ?>

	<span class="small_text"><a href="settings.php" target="middle_frame"><?php echo Translate("Settings", 1); ?></a> | <a href="users.php" target="middle_frame"><?php echo Translate("Users", 1); ?></a></span>
	<br><br>

<?php } ?>

<table style="font-size:10px;text-align:center" summary="">
	<tr><td style="color:#808080">OpenBookings.org <?php echo param_extract("app_version"); ?><br>&copy; 2005-<?php echo date("Y"); ?> J&eacute;r&ocirc;me Roger</td></tr>
	<tr><td><a href="http://www.openbookings.org" target="_blank">http://www.openbookings.org</a></td></tr>
</table>

</center>

<iframe id="iframe_action" name="iframe_action"></iframe>

<form id="form_action" method="post" action="actions.php" target="iframe_action">
	<input type="hidden" id="action_" name="action_" value="">
</form>

<script type="text/javascript"><!--
$("screen_width").value = screen.width;
$("screen_height").value = screen.height;
--></script>

</body>

</html>
