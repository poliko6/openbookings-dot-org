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

	<style type="text/css">
		#iframe_action {
			width:500px;
			height:200px;
			visibility:visible;
		}
	</style>

	<script type="text/javascript"><!--

		function $(id) { return document.getElementById(id); }

		function changeColumns() {
			$("languages_form").action = "localize.php";
			$("languages_form").target = "";
			$("languages_form").submit();
		}

		function updateSentence(lang_id, e) {
			if(e.keyCode == 13) {
				$("languages_form").action = "actions.php";
				$("languages_form").target = "iframe_action";
				$("action_").value = "update_localization";
				$("lang_id").value = lang_id;
				$("languages_form").submit();
				$("localize_to_" + lang_id).style.background = "white";
			} else {
				$("localize_to_" + lang_id).style.background = "orange";
			}
		}

		function exportLocalization() {
			$("languages_form").action = "actions.php";
			$("languages_form").target = "iframe_action";
			$("action_").value = "export_localization";
			$("languages_form").submit();
		}

	--></script>

</head>

<body>

	<div class="global" style="width:800px">

		<form method="post" enctype="multipart/form-data" action="actions.php" target="iframe_action">

			<input type="hidden" name="MAX_FILE_SIZE" value="30000">
			<input type="hidden" name="action_" value="import_localization">

			<table style="width:100%"><tr>
				<td><span class="big_text"><?php echo Translate("Localization", 0); ?></span></td>
				<td style="text-align:right">Import from CSV file :</td>
				<td><input type="file" name="localization_file"></td>
				<td><button type="submit">Import</button></td>
				<td style="text-align:right"><button type="button" onClick="exportLocalization()"><?php echo Translate("Export to CSV file", 0); ?></button></td>
			</tr></table>

		</form>

		<form id="languages_form" method="post" action="localize.php">

		<center>

		<table class="localize_list" style="text-align:left">
			<tr>
				<th style="width:400px">Source language : <select id="localize_from" name="localize_from" onChange="changeColumns()"><?php echo $languages_list; ?></select></th>
				<th style="width:400px">Target language : <select id="localize_to" name="localize_to" onChange="changeColumns()"><?php echo $languages_list; ?></select></th>
			</tr><tr>
				<td colspan="2" style="text-align:center">
					<input type="checkbox" id="show_only_missing" name="show_only_missing" <?php echo $show_only_missing; ?> onChange="changeColumns()"><?php echo Translate("Show only missing vocabulary", 0); ?>
				</td>
			</tr><tr>
				<td></td>
				<th><span class="small_text"><?php echo Translate("Press enter to validate each modification", 0); ?></span></th>
			</tr>

			<?php while($vocabulary_ = fetch_array($vocabulary)) { ?><tr>
				<td><?php echo $vocabulary_[$localize_from]; ?></td>
				<td>
					<input class="localize_input" id="localize_to_<?php echo $vocabulary_["lang_id"]; ?>" name="localize_to_<?php echo $vocabulary_["lang_id"]; ?>" onKeyPress="updateSentence(<?php echo $vocabulary_["lang_id"]; ?>,event)" value="<?php echo $vocabulary_[$localize_to]; ?>">
				</td>
			</tr><?php } ?>

		</table>

		</center>

		<input type="hidden" id="lang_id" name="lang_id">
		<input type="hidden" id="action_" name="action_" value="">

	</div>

</form>

<iframe id="iframe_action" name="iframe_action"></iframe>

<script type="text/javascript"><!--
	$("localize_from").value = "<?php echo $localize_from; ?>";
	$("localize_to").value = "<?php echo $localize_to; ?>";
--></script>

</body>

</html>