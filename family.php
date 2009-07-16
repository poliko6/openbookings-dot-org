<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	family.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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
	
	
	
	/* checks posted vars
	$post_vars_array = array();
	
	$allowed_actions = array("insert_new_family","update_family","delete_family");
	
	$post_vars_array["action_"] = array("sql", $_POST["action_"], "string", "", "", $allowed_actions, "", "action_");
	$post_vars_array["sort_order"] = array("sql", $_POST["sort_order"], "int", "1", "", "", "", "sort_order");
	$post_vars_array["postype"] = array("sql", $_POST["postype"], "int", "-1", "0", "", "", "postype");
	$post_vars_array["family_id"] = array("sql", $_POST["family_id"], "int", "1", "", "", "", "postype");
	$post_vars_array["family_name"] = array("", $_POST["family_name"], "string", "1", "50", "", "", "postype");
	$post_vars_array["previous_sort_order"] = array("", $_POST["previous_sort_order"], "int", "1", "", "", "", "sort_order");
	
	$post_vars_array = checkVars($post_vars_array); */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">

<title><?php echo $app_title . " :: " . Translate("Family", 1); ?></title>

	<script type="text/javascript"><!--
		<?php includeCommonScripts(); ?>
	--></script>
	
<?php

	$family_id = checkVar("sql", $_POST["family_id"], "int", 1, "", "0", "");

	if($family_id == "0") {

		$action_ = "insert_new_family";

		$family_name = "";
		$sort_order = "";

	} else {

		$action_ = "update_family";

		$sql  = "SELECT family_id, family_name, sort_order ";
		$sql .= "FROM rs_param_families WHERE family_id = " . $family_id . ";";
		$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);

		$family_name = $temp_["family_name"];
		$sort_order = $temp_["sort_order"];
	}

	// actions
	if(isset($_POST["action_"])) {

		// postype : "before" = -1, "after" = 0
		$order = intval($_POST["sort_order"]) + intval($_POST["postype"]) +1;

		switch($_POST["action_"]) {

			case "insert_new_family":

			// punch hole in sort #
			$sql = "UPDATE rs_param_families SET sort_order = sort_order + 1 WHERE sort_order >= " . $order . ";";
			db_query($database_name, $sql, "no", "no");

			$sql  = "INSERT INTO rs_param_families ( family_name, sort_order ) ";
			$sql .= "VALUES ( ";
			$sql .= "'" . $_POST["family_name"] . "', ";
			$sql .= "'" . $order . "' );";
			db_query($database_name, $sql, "no", "no");

			// gets the new family_id
			$sql = "SELECT family_id FROM rs_param_families WHERE order = " . $order . ";";
			$temp = db_query($database_name, $sql, "no", "no");
			if($temp_ = fetch_array($temp)) { $family_id = $temp_["family_id"]; }

			break;

			case "update_family":

			// removes sort #
			$sql = "UPDATE rs_param_families SET sort_oprevious_sort_orderrder = 0 WHERE sort_order = " . $_POST["previous_sort_order"] . ";";
			db_query($database_name, $sql, "no", "no");

			$sql = "SELECT family_id, sort_order FROM rs_param_families WHERE sort_order <> 0 ORDER BY sort_order;";
			$temp = db_query($database_name, $sql, "no", "no");

			if($order > 0) { $s = 0; } else { $s = 1; }

			while($temp_ = fetch_array($temp)) { $s++;
				if($temp_["sort_order"] <= $order || $order == 0) {
					$sql = "UPDATE rs_param_families SET sort_order = " . $s . " WHERE family_id = " . $temp_["family_id"] . ";";

					db_query($database_name, $sql, "no", "no");
				}
			}

			if($order == 0) { $order = 1; }

			$sql = "UPDATE rs_param_families SET ";
			$sql .= "family_name = '" . $_POST["family_name"] . "', ";
			$sql .= "sort_order = '" . $order . "' ";
			$sql .= "WHERE family_id = " . $_POST["family_id"] . ";";
			db_query($database_name, $sql, "no", "no");

			break;

			case "delete_family":

			// moves the objects to the <unclassified> temporary family
			$sql = "UPDATE rs_data_objects SET family_id = 255 WHERE family_id = " . $_POST["family_id"] . ";";
			db_query($database_name, $sql, "no", "no");

			$sql = "DELETE FROM rs_param_families WHERE family_id = " . $_POST["family_id"] . ";";
			db_query($database_name, $sql, "no", "no");

			$sql = "SELECT family_id FROM rs_param_families ORDER BY sort_order LIMIT 1;";
			$temp = db_query($database_name, $sql, "no", "no");
			if($temp_ = fetch_array($temp)) { $family_id = $temp_["family_id"]; } else { $family_id = 0; }

		} // switch
?>

	<script type="text/javascript"><!--
		top.frames[0].location = "menu.php?family_id=<?php echo $family_id; ?>";
		top.frames[1].location = "intro.php";
	--></script>

</head>

<body>

</body>

</html>

<?php

	} else { // !isset($_POST["action_"])

		// extracts families list
		$sql  = "SELECT sort_order, family_name FROM rs_param_families ";
		$sql .= "WHERE family_id <> " . $family_id . " ";
		$sql .= "ORDER BY sort_order;";
		$temp = db_query($database_name, $sql, "no", "no");

		$families_list = "";

		while($temp_ = fetch_array($temp)) {
			if($temp_["sort_order"] == $sort_order) { $selected = " selected"; } else { $selected = ""; }
			$families_list .= "<option value='" . $temp_["sort_order"] . "'" . $selected . ">" . $temp_["family_name"] . "</option>";
		}
?>

	<link rel="stylesheet" type="text/css" href="styles.php">

	<?php if($family_id != "0" && $_COOKIE["bookings_profile_id"] == "4") { ?><script type="text/javascript"><!--
		function DeleteFamily() {
			if(window.confirm("<?php echo Translate("WARNING ! Deleting this family will move all its objects in a temporary 'Unclassified' family", 0); ?>")) {
				$("action_").value = "delete_family";
				$("form_family").submit();
			}
		}
	--></script><?php } ?>

</head>

<body style="text-align:center; margin-top:10px">

<center>

<form id="form_family" name="form_family" method="post" action="family.php">

<table class="table3"><tr><td>

	<span class="big_text"><?php echo Translate("Family", 1); ?></span><br>

	<div class="colorframe">

		<div class="marginframe">

		<b><?php echo Translate("Family name", 1); ?></b><br>
		<input id="family_name" name="family_name" style="width:200px; text-align:center" value="<?php echo $family_name; ?>">

		<br><br>

		<b><?php echo Translate("Position in list", 1); ?></b><br>

		<input type="radio" id="postype_before" name="postype" value="-1" checked><?php echo Translate("Before", 1); ?>
		<input type="radio" id="postype_after" name="postype" value="0"><?php echo Translate("After", 1); ?>

		<br>

		<select id="sort_order" name="sort_order" style="width:200px"><?php echo $families_list; ?></select>

		</div>

	</div>

</td></tr>

<tr><td style="height:10px"></td></tr>

<tr><td style="text-align:center">

<button style="width:100px" type="submit"<?php if(!isset($_COOKIE["bookings_profile_id"]) || $_COOKIE["bookings_profile_id"] != "4") { echo " disabled"; } ?>><?php echo Translate("OK", 1); ?></button>
&nbsp;&nbsp;
<button style="width:100px" type="button" onClick="DeleteFamily()"<?php if(!isset($_COOKIE["bookings_profile_id"]) || $_COOKIE["bookings_profile_id"] != "4") { echo " disabled"; } ?>><?php echo Translate("Delete", 1); ?></button>

</td></tr></table>

<input type="hidden" id="previous_sort_order" name="previous_sort_order" value="<?php echo $sort_order; ?>">
<input type="hidden" id="family_id" name="family_id" value="<?php echo $family_id; ?>">
<input type="hidden" id="action_" name="action_" value="<?php echo $action_; ?>">

</form>

</center>

</body></html>

<?php } // action_ ?>
