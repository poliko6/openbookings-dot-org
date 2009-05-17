<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	object.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . Translate("Object", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<?php

	$object_id = $_REQUEST["object_id"];
	$reload_location = "intro.php";
	$error_msg = ""; $script = "";

	if($object_id == "0") { // new object -> using default values

		$action_ = "insert_new_object";

		$title = Translate("New object", 1);
		$object_name = "";
		$family_id = $_REQUEST["family_id"];
		$resp_code = "0";
		$activity_start = "08:00";
		$activity_end = "18:00";
		$activity_step = 15; // minutes
		$email_bookings_checked = " checked";
		$booking_method = "time_based";

		$misc_info = "";

		$everyone_generic_permissions = "none";
		$guests_generic_permissions = "view";
		$users_generic_permissions = "modify";

	} else {

		$action_ = "update_object";

		// get object infos
		$sql  = "SELECT object_id, object_name, family_id, activity_start, activity_end, activity_step, booking_method, email_bookings, misc_info ";
		$sql .= "FROM rs_data_objects WHERE object_id = " . $object_id . ";";
		$object = db_query($database_name, $sql, "no", "no"); $object_ = fetch_array($object);

		$title = Translate("Object", 1) . " : " . $object_["object_name"];
		$object_name = $object_["object_name"];
		$family_id = $object_["family_id"];
		if($object_["activity_start"] == "" || is_null($object_["activity_start"])) { $activity_start = "08:00"; } else { $activity_start = date("H:i", strtotime("1970-01-01 " . $object_["activity_start"])); }
		if($object_["activity_end"] == "" || is_null($object_["activity_end"])) { $activity_end = "18:00"; } else { $activity_end = date("H:i", strtotime("1970-01-01 " . $object_["activity_end"])); }
		if($object_["activity_step"] == "" || is_null($object_["activity_step"])) { $activity_step = 15; } else { $activity_step = $object_["activity_step"]; }
		$booking_method = $object_["booking_method"];
		if($object_["email_bookings"] == "yes") { $email_bookings_checked = " checked"; } else { $email_bookings_checked = ""; }
		$misc_info = stripslashes($object_["misc_info"]);

		$everyone_generic_permissions = getGenericPermissions(1, $object_id);
		$guests_generic_permissions = getGenericPermissions(2, $object_id);
		$users_generic_permissions = getGenericPermissions(3, $object_id);
	}

	// actions
	if(isset($_REQUEST["action_"])) { switch($_REQUEST["action_"]) {

		case "insert_new_object":

			// Checks fields content
			if($_REQUEST["object_name"] == "" ) { $error_msg = Translate("You must name your object to record it", 1); }

			// Checks for already used object name
			$sql = "SELECT object_id FROM rs_data_objects WHERE object_name = '" . $_REQUEST["object_name"] . "';";
			if($temp = fetch_array(db_query($database_name, $sql, "no", "no"))) { $error_msg = Translate("This name is already used by another object", 1); }

			if(isset($_POST["email_bookings"])) { $email_bookings = "yes"; } else { $email_bookings = "no"; }

			if($error_msg == "") {

				// Creates a random code
				$rand_code = rand(0,65535);

				// Inserts the new object associated with the random code
				$sql  = "INSERT INTO rs_data_objects ( rand_code, object_name, family_id, activity_start, activity_end, activity_step, booking_method, email_bookings, misc_info ) VALUES ( ";
				$sql .= $rand_code . ", ";
				$sql .= "'" . $_REQUEST["object_name"] . "', ";
				$sql .= $_REQUEST["family_id"] . ", ";
				$sql .= "'" . $_REQUEST["activity_start"] . "', ";
				$sql .= "'" . $_REQUEST["activity_end"] . "', ";
				$sql .= $_REQUEST["activity_step"] . ", ";
				$sql .= "'" . $_REQUEST["booking_method"] . "', ";
				$sql .= "'" . $email_bookings . "', ";
				$sql .= "'" . addslashes($_REQUEST["misc_info"]) . "' );";
				db_query($database_name, $sql, "no", "no");

				// Gets new object id using the random code
				$sql = "SELECT object_id FROM rs_data_objects WHERE rand_code = " . $rand_code . ";";
				$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp); $object_id = $temp_["object_id"];

				// Clears random code
				$sql = "UPDATE rs_data_objects SET rand_code = '' WHERE rand_code = " . $rand_code . ";";
				db_query($database_name, $sql, "no", "no");

				// inserts default permissions for the new object
				$sql  = "INSERT INTO rs_data_permissions ( object_id, profile_id, permission ) VALUES ";
				$sql .= "( " . $object_id . ", 1, '" . $_REQUEST["everyone"] . "'), "; // everyone
				$sql .= "( " . $object_id . ", 2, '" . $_REQUEST["guests"] . "'), "; // guests
				$sql .= "( " . $object_id . ", 3, '" . $_REQUEST["users"] . "'), "; // authenticated users
				$sql .= "( " . $object_id . ", 4, 'manage' ) "; // administrators
				db_query($database_name, $sql, "no", "no");

				$reload_location = "object.php?object_id=" . $object_id;
			}

		break;

		case "update_object":

			// checks fields content
			if($_REQUEST["object_name"] == "" ) { $error_msg = Translate("You must name your object to record it", 1); }

			// checks for already used object name
			$sql = "SELECT object_id FROM rs_data_objects WHERE object_name = '" . $_REQUEST["object_name"] . "' AND object_id <> " . $object_id . ";";
			if($temp = fetch_array(db_query($database_name, $sql, "no", "no"))) { $error_msg = Translate("This name is already used by another object", 1); }

			if(isset($_POST["email_bookings"])) { $email_bookings = "yes"; } else { $email_bookings = "no"; }

			if($error_msg == "") {
				$sql  = "UPDATE rs_data_objects SET ";
				$sql .= "object_name = '" . $_REQUEST["object_name"] . "', ";
				$sql .= "family_id = " . $_REQUEST["family_id"] . ", ";
				$sql .= "activity_start = '" . $_REQUEST["activity_start"] . "', ";
				$sql .= "activity_end = '" . $_REQUEST["activity_end"] . "', ";
				$sql .= "activity_step = " . $_REQUEST["activity_step"] . ", ";
				$sql .= "booking_method = '" . $_REQUEST["booking_method"] . "', ";
				$sql .= "email_bookings = '" . $email_bookings . "', ";
				$sql .= "misc_info = '" . addslashes($_REQUEST["misc_info"]) . "' ";
				$sql .= "WHERE object_id = " . $object_id  . ";";
				db_query($database_name, $sql, "no", "no");

				// update generic permissions
				$sql = "UPDATE rs_data_permissions SET permission = '" . $_REQUEST["everyone"] . "' WHERE object_id = " . $object_id . " AND profile_id = 1;";
				db_query($database_name, $sql, "no", "no");

				$sql = "UPDATE rs_data_permissions SET permission = '" . $_REQUEST["guests"] . "' WHERE object_id = " . $object_id . " AND profile_id = 2;";
				db_query($database_name, $sql, "no", "no");

				$sql = "UPDATE rs_data_permissions SET permission = '" . $_REQUEST["users"] . "' WHERE object_id = " . $object_id . " AND profile_id = 3;";
				db_query($database_name, $sql, "no", "no");

				for($n=1; $n<=$_REQUEST["custom_permissions_nb"]; $n++) {
					$sql = "UPDATE rs_data_permissions SET permission = '" . $_REQUEST["custom_permission_" . $n] . "' WHERE permission_id = " . $_REQUEST["permission_id_" . $n] . ";";
					db_query($database_name, $sql, "no", "no");
				}

				$reload_location = "object.php?object_id=" . $object_id;
			}

		break;

		case "delete_object":

			// deletes all object's bookings
			$sql = "DELETE FROM rs_data_bookings WHERE object_id = " . $object_id  . ";";
			db_query($database_name, $sql, "no", "no");

			// deletes all object's permissions
			$sql = "DELETE FROM rs_data_permissions WHERE object_id = " . $object_id  . ";";
			db_query($database_name, $sql, "no", "no");

			// deletes the object
			$sql = "DELETE FROM rs_data_objects WHERE object_id = " . $object_id  . ";";
			db_query($database_name, $sql, "no", "no");

			$reload_location = "intro.php";

		break;

		case "add_custom_permission":

			if($_REQUEST["new_user_id"] != "" && $_REQUEST["new_custom_permission"] != "") {
				$sql = "INSERT INTO rs_data_permissions ( object_id, user_id, profile_id, permission ) VALUES ( " . $object_id . ", " . $_REQUEST["new_user_id"] . ", 0, '" . $_REQUEST["new_custom_permission"] .  "' );";
				db_query($database_name, $sql, "no", "no");
			}

			$reload_location = "object.php?object_id=" . $object_id;

			break;

		case "delete_custom_permission":

			$sql = "DELETE FROM rs_data_permissions WHERE permission_id = " . $_REQUEST["permission_id"] . " AND object_id = " . $object_id . ";";
			db_query($database_name, $sql, "no", "no");

			$reload_location = "object.php?object_id=" . $object_id;

			break;

		} // switch($_REQUEST["action_"])

		if($error_msg != "") {
			// displays error and "back" button
			echo "<span style=\"color:#ff0000\">" . $error_msg . "</span><br><br>\n";
			echo "<button type=\"button\" onClick=\"document.location='object.php?object_id=" . $object_id . "'\">" . Translate("Back") . "</button>";
		} else {
			// no errors, action successful, refreshing both menu and object frames
			echo "<script type=\"text/javascript\"><!--\n";
				echo "top.frames[0].location = \"menu.php?object_id=" . $object_id . "&family_id=" . $family_id . "\";\n";
				echo "top.frames[1].location = \"" . $reload_location . "\";\n";
			echo "--></script>\n";
		}

	} else { // !isset($_REQUEST["action_"])

	// extracts object families list
	$sql = "SELECT family_id, family_name, sort_order FROM rs_param_families ORDER BY sort_order;";
	$families = db_query($database_name, $sql, "no", "no");

	$families_list = "";
	if($family_id == 255) { $families_list .= "<option value=\"999\" selected>&lt;" . Translate("Unclassified", 0) . "&gt;</option>"; }

	while($families_ = fetch_array($families)) {
		$families_list .= "<option value=\"" . $families_["family_id"] . "\"";
		if($families_["family_id"] == $family_id) { $families_list .= " selected"; }
		$families_list .= ">" . $families_["family_name"] . "</option>";
	}

	// extracts users
	$sql = "SELECT rs_data_users.user_id, rs_data_users.last_name, rs_data_users.first_name ";
	$sql .= "FROM rs_data_users ORDER BY last_name, first_name;";
	$users = db_query($database_name, $sql, "no", "no");

	$users_list = "<option value=\"\">-</option>";

	while($users_ = fetch_array($users)) { $users_list .= "<option value=\"" . $users_["user_id"] . "\">" . $users_["first_name"] . " " . $users_["last_name"] . "</option>"; }

	// extracts users list
	$sql = "SELECT rs_data_users.user_id, rs_data_users.last_name, rs_data_users.first_name ";
	$sql .= "FROM rs_data_users ORDER BY last_name, first_name;";
	$users = db_query($database_name, $sql, "no", "no");

	$custom_permissions_table = ""; $custom_permissions_script = ""; $n = 0;

	$sql = "SELECT rs_data_users.first_name, rs_data_users.last_name, ";
	$sql .= "rs_data_permissions.permission_id, rs_data_permissions.permission ";
	$sql .= "FROM (rs_data_permissions INNER JOIN rs_data_users ON rs_data_permissions.user_id = rs_data_users.user_id) ";
	$sql .= "INNER JOIN rs_param_profiles ON rs_data_users.profile_id = rs_param_profiles.profile_id ";
	$sql .= "WHERE rs_data_permissions.object_id = " . $object_id . " ";
	$sql .= "ORDER BY rs_data_users.first_name, rs_data_users.last_name;";

	$custom_permissions = db_query($database_name, $sql, "no", "no");

	while($custom_permissions_ = fetch_array($custom_permissions)) { $n++;
		$custom_permissions_table .= "<tr>\n";
		$custom_permissions_table .= "<td style=\"text-align:left\">";
		$custom_permissions_table .= "<a href=\"JavaScript:DeleteCustomPermission(" . $custom_permissions_["permission_id"] . ")\"><img src=\"pictures/delete.gif\" alt=\"" . Translate("Delete", 1) . "\"></a>&nbsp;";
		$custom_permissions_table .= unDuplicateName($custom_permissions_["first_name"], $custom_permissions_["last_name"]);
		$custom_permissions_table .= "<input type=\"hidden\" id=\"permission_id_" . $n . "\" name=\"permission_id_" . $n . "\" value=\"" . $custom_permissions_["permission_id"] . "\"></td>\n";
		$custom_permissions_table .= "<td><input type=\"radio\" id=\"none_" . $n . "\" name=\"custom_permission_" . $n . "\" value=\"none\">\n";
		$custom_permissions_table .= "<td><input type=\"radio\" id=\"view_" . $n . "\" name=\"custom_permission_" . $n . "\" value=\"view\">\n";
		$custom_permissions_table .= "<td><input type=\"radio\" id=\"add_" . $n . "\" name=\"custom_permission_" . $n . "\" value=\"add\">\n";
		$custom_permissions_table .= "<td><input type=\"radio\" id=\"modify_" . $n . "\" name=\"custom_permission_" . $n . "\" value=\"modify\">\n";
		$custom_permissions_table .= "<td><input type=\"radio\" id=\"manage_" . $n . "\" name=\"custom_permission_" . $n . "\" value=\"manage\">\n";
		$custom_permissions_table .= "<td>&nbsp;</td>";
		$custom_permissions_table .= "</tr>\n";
		$custom_permissions_script .= "$(\"" . $custom_permissions_["permission"] . "_" . $n . "\").checked = true;\n";
	}

	$custom_permissions_nb = $n;
?>

<script type="text/javascript"><!--

	<?php includeCommonScripts(); ?>

	function CheckFormat(value_to_check, filter){
	   var re = new RegExp(filter,"i");
	   var r = re.exec(value_to_check);
	   return(r);
	}

	function SubmitForm() {

		var allow_submit = true;

		if(CheckFormat($("activity_start").value, "^(([0-9]{1})|([0-1][0-9])|([1-2][0-3])):([0-5][0-9])$") == null) {
			alert("<?php echo Translate("Activity start format is not valid (should be hh:mm)", 0); ?>");
			allow_submit = false;
		}

		if(CheckFormat($("activity_end").value, "^(([0-9]{1})|([0-1][0-9])|([1-2][0-3])):([0-5][0-9])$") == null) {
			alert("<?php echo Translate("Activity end format is not valid (should be hh:mm)", 0); ?>");
			allow_submit = false;
		}

		if(CheckFormat($("activity_step").value, "^[1-9][0-9]{0,2}$") == null) {
			alert("<?php echo Translate("Activity step format is not valid (should be an integer between 1 and 999)", 0); ?>");
			allow_submit = false;
		}

		if(allow_submit) { $("form_object").submit(); }
	}

	function DeleteObject() {
		rep = window.confirm("<?php echo Translate("WARNING ! Deleting an object will destroy all attached bookings", 0); ?>.\n\n <?php echo Translate("Do you really want to delete this object ?", 0); ?>");
		if(rep) { $("action_").value = "delete_object"; $("form_object").submit(); }
	}

	function AddCustomPermission() {
		$("action_").value = "add_custom_permission";
		$("form_object").submit();
	}

	function DeleteCustomPermission(permission_id) {
		document.location = "object.php?object_id=<?php echo $object_id; ?>&action_=delete_custom_permission&permission_id=" + permission_id;
	}

--></script>

</head>

<body>

<span class="big_text"><?php echo $title; ?></span>

<hr>

<center>

<form id="form_object" name="form_object" method="post" action="object.php">

<table class="table3" style="font-weight:bold"><tr>

	<td>
		<?php echo Translate("Family", 1); ?><br>
		<select id="family_id" name="family_id" style="width:240px"><?php echo $families_list; ?></select>
	</td>

	<td rowspan="4" style="width:30px"></td>
	<td rowspan="4" valign="top">
		<?php echo Translate("Remarks", 1); ?><br>
		<textarea id="misc_info" name="misc_info" style="width:300px; height:100px"><?php echo $misc_info; ?></textarea>
	</td>

</tr><tr>

	<td><?php echo Translate("Object name", 1); ?><br><input type="text" id="object_name" name="object_name" style="width:240px" value="<?php echo $object_name; ?>"></td>

</tr><tr>

	<td style="font-weight:bold"><?php echo Translate("Booking method", 1); ?><br><select id="booking_method" name="booking_method" style="width:240px"><option value="time_based"><?php echo Translate("Time-based", 1); ?></option><option value="stacking"><?php echo Translate("Stacking", 1); ?></option></select></td>

</tr><tr>

<td colspan="3">

	<table class="table3"><tr>
		<td style="font-weight:bold"><?php echo Translate("Activity start", 1); ?> <br><input id="activity_start" name="activity_start" style="text-align:center; width:80px" value="<?php echo $activity_start; ?>"></td>
		<td style="width:10px"></td>
		<td style="font-weight:bold"><?php echo Translate("Activity end", 1); ?><br><input id="activity_end" name="activity_end" style="text-align:center; width:80px" value="<?php echo $activity_end; ?>"></td>
		<td style="width:10px"></td>
		<td><span style="font-weight:bold"><?php echo Translate("Activity step", 1); ?> <br><input id="activity_step" name="activity_step" style="text-align:center; width:40px" value="<?php echo $activity_step; ?>"></span>&nbsp;<?php echo Translate("minutes", 1); ?></td>
		<td style="width:10px"></td>
		<td style="font-weight:bold" valign="bottom"><input type="checkbox" id="email_bookings" name="email_bookings"<?php echo $email_bookings_checked; ?>>&nbsp;<?php echo Translate("Use email to manage bookings", 1); ?></td>
	</tr></table>
	<span class="small_text" style="color:#808080"><?php echo Translate("Tip : for all day long activity, set start and end to 00:00", 1); ?></span><br>
</td>

</tr></table>

<br>

<table style="text-align:left; font-weight:bold"><tr><td>

	<?php echo Translate("Generic booking permissions", 1); ?><br>

	<table class="table2" style="width:600px; text-align:center"><tr>
		<th>&nbsp;</th>
		<th><?php echo Translate("None", 1); ?></th>
		<th><?php echo Translate("View", 1); ?></th>
		<th><?php echo Translate("Add", 1); ?></th>
		<th><?php echo Translate("Modify", 1); ?></th>
	</tr><tr>
		<th><?php echo Translate("Everyone", 1); ?></th>
		<td><input type="radio" id="everyone_none" name="everyone" value="none"></td>
		<td><input type="radio" id="everyone_view" name="everyone" value="view"></td>
		<td><input type="radio" id="everyone_add" name="everyone" value="add"></td>
		<td><input type="radio" id="everyone_modify" name="everyone" value="modify"></td>
	</tr><tr>
		<th><?php echo Translate("Guests", 1); ?></th>
		<td><input type="radio" id="guests_none" name="guests" value="none"></td>
		<td><input type="radio" id="guests_view" name="guests" value="view"></td>
		<td><input type="radio" id="guests_add" name="guests" value="add"></td>
		<td><input type="radio" id="guests_modify" name="guests" value="modify"></td>
	</tr><tr>
		<th><?php echo Translate("Users", 1); ?></th>
		<td><input type="radio" id="users_none" name="users" value="none"></td>
		<td><input type="radio" id="users_view" name="users" value="view"></td>
		<td><input type="radio" id="users_add" name="users" value="add"></td>
		<td><input type="radio" id="users_modify" name="users" value="modify"></td>
	</tr></table>

</td></tr></table>

<br>

<?php if($object_id != "0") { ?>

<table style="text-align:left; font-weight:bold"><tr><td>

	<?php echo Translate("Custom booking permissions (overrides generic booking permissions)", 1); ?>

	<table class="table2" style="width:600px; text-align:center">
	<tr>
		<th><?php echo Translate("User", 1); ?></th>
		<th><?php echo Translate("None", 1); ?></th>
		<th><?php echo Translate("View", 1); ?></th>
		<th><?php echo Translate("Add", 1); ?></th>
		<th><?php echo Translate("Modify", 1); ?></th>
		<th><?php echo Translate("Manage", 1); ?></th>
		<th>&nbsp;</th>
	</tr>

	<?php echo $custom_permissions_table; ?>

	<tr>
		<td><select id="new_user_id" name="new_user_id"><?php echo $users_list; ?></select></td>
		<td><input type="radio" id="new_none" name="new_custom_permission" value="none"></td>
		<td><input type="radio" id="new_view" name="new_custom_permission" value="view"></td>
		<td><input type="radio" id="new_add" name="new_custom_permission" value="add"></td>
		<td><input type="radio" id="new_modify" name="new_custom_permission" value="modify"></td>
		<td><input type="radio" id="new_manage" name="new_custom_permission" value="manage"></td>
		<td><button type="button" onClick="AddCustomPermission()"><?php echo Translate("Add", 1); ?></button></td>
	</tr>
	</table>

</td></tr></table>

<?php } ?>

<br>

<table><tr>
<td><button type="button" style="width:100px"<?php if(!isset($_COOKIE["bookings_profile_id"]) || $_COOKIE["bookings_profile_id"] != "4") { echo " disabled"; } ?> onClick="SubmitForm()"><?php echo Translate("OK", 1); ?></button></td>

<?php if($object_id != "0") { ?>
<td style="width:10px"></td>
<td><button type="button" onClick="DeleteObject()" style="width:100px"<?php if(!isset($_COOKIE["bookings_profile_id"]) || $_COOKIE["bookings_profile_id"] != "4") { echo " disabled"; } ?>><?php echo Translate("Delete", 1); ?></button></td>
<?php } ?>
</tr></table>

<input type="hidden" id="action_" name="action_" value="<?php echo $action_; ?>">
<input type="hidden" id="user_id" name="user_id" value="<?php echo $_COOKIE["bookings_user_id"]; ?>">
<input type="hidden" id="object_id" name="object_id" value="<?php echo $object_id; ?>">
<input type="hidden" id="custom_permissions_nb" name="custom_permissions_nb" value="<?php echo $custom_permissions_nb; ?>">

</form>

</center>

<script type="text/javascript"><!--

	$("booking_method").value = "<?php echo $booking_method; ?>";
	$("everyone_<?php echo $everyone_generic_permissions; ?>").checked = true;
	$("guests_<?php echo $guests_generic_permissions; ?>").checked = true;
	$("users_<?php echo $users_generic_permissions; ?>").checked = true;

	<?php echo $custom_permissions_script; ?>

--></script>

</body>

</html>

<?php } ?>
