<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	my_bookings.php - This file is part of OpenBookings.org (http://www.openbookings.org)

    OpenBookings.org is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    OpenBookings.org is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenBookings.org; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA */

	require_once "config.php";
	require_once "connect_db.php";
	require_once "functions.php";

	CheckCookie(); // Resets app to the index page if timeout is reached. This function is implemented in functions.php

	$languages_list = "";

	if(isset($_POST["localize_from"])) { $localize_from = $_POST["localize_from"]; } else { $localize_from = "english"; }
	if(isset($_POST["localize_to"])) { $localize_to = $_POST["localize_to"]; } else { $localize_to = "french"; }
	if(isset($_POST["show_only_missing"])) { $show_only_missing = " checked"; } else { $show_only_missing = ""; }

	// extracts availables languages
	$sql = "SHOW COLUMNS FROM rs_param_lang";
	$columns = db_query($database_name, $sql, "no", "no");

	while($columns_ = fetch_array($columns)) {

		if($columns_["Field"] != "lang_id") {
			$languages_list .= "<option value=\"" . $columns_["Field"] . "\">" . $columns_["Field"] . "</option>";
		}
	}

	// extracts vocabulary
	$sql  = "SELECT lang_id, " . $localize_from . ", " . $localize_to . " ";
	$sql .= "FROM rs_param_lang WHERE LENGTH(" . $localize_from . ") ";
	if($show_only_missing != "") { $sql .= "AND (" . $localize_to . " = '' OR " . $localize_to . " IS NULL) "; }
	$sql .= "ORDER BY " . $localize_from . ";";
	$vocabulary = db_query($database_name, $sql, "no", "no");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

	<title><?php echo Translate("Localization", 1); ?></title>

	<link rel="stylesheet" type="text/css" href="styles.php">

	<script type="text/javascript"><!--

		function changeColumns() { document.getElementById("languages_form").submit(); }

		function updateSentence(lang_id, e) {
			if(e.keyCode == 13) {
				document.getElementById("languages_form").action = "actions.php";
				document.getElementById("languages_form").target = "iframe_action";
				document.getElementById("action_").value = "update_localization";
				document.getElementById("lang_id").value = lang_id;
				document.getElementById("languages_form").submit();
				document.getElementById("localize_to_" + lang_id).style.background = "white";
			} else {
				document.getElementById("localize_to_" + lang_id).style.background = "orange";
			}
		}

	--></script>

</head>

<body style="margin:10px; text-align:left">

<form id="languages_form" method="post" action="localize.php">

	<table class="table6" style="text-align:left" summary="">
		<tr>
			<th style="text-align:left"><select id="localize_from" name="localize_from" onChange="changeColumns()"><?php echo $languages_list; ?></select></th>
			<th style="text-align:left"><select id="localize_to" name="localize_to" onChange="changeColumns()"><?php echo $languages_list; ?></select>&nbsp;&nbsp;<input type="checkbox" id="show_only_missing" name="show_only_missing" <?php echo $show_only_missing; ?> onChange="changeColumns()"><?php echo Translate("Show only missing vocabulary", 0); ?></th>
		</tr>

		<?php while($vocabulary_ = fetch_array($vocabulary)) { ?><tr>
			<td><?php echo $vocabulary_[$localize_from]; ?></td>
			<td>
				<input id="localize_to_<?php echo $vocabulary_["lang_id"]; ?>" name="localize_to_<?php echo $vocabulary_["lang_id"]; ?>" style="width:400px" onKeyPress="updateSentence(<?php echo $vocabulary_["lang_id"]; ?>,event)" value="<?php echo $vocabulary_[$localize_to]; ?>">
			</td>
		</tr><?php } ?>

	</table>

	<input type="hidden" id="lang_id" name="lang_id">
	<input type="hidden" id="action_" name="action_" value="">

</form>

<iframe id="iframe_action" name="iframe_action" style="visibility:hidden;width:0px;height:0px"></iframe>

<script type="text/javascript"><!--
	document.getElementById("localize_from").value = "<?php echo $localize_from; ?>";
	document.getElementById("localize_to").value = "<?php echo $localize_to; ?>";
--></script>

</body>

</html>
