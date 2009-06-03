<?php

	/* OpenBookings.org - Copyright (C) 2005 Jérôme ROGER (jerome@openbookings.org)

	actions.php - This file is part of OpenBookings.org (http://www.openbookings.org)

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

	$message = ""; $script = "";

	$post_action = validateInput("", $_POST["action"], "string", 0, 0)

	switch($post_action) {

		case "connect": // **********************************************************************************************

		$post_username = validateInput("post_username", $_POST["username"], "string", 5, 20);
		$post_password = validateInput("post_password", $_POST["password"], "string", 5, 20);

		$sql  = "SELECT user_id, profile_id, locked, language, date_format, user_timezone FROM rs_data_users ";
		$sql  .= "WHERE login = '" . toDb($post_username) . "' ";
		$sql .= "AND password = '" . toDb($post_password) . "' ";
		$sql .= "AND profile_id >= " . toDb($application_access_level) . ";";
		$user = db_query($database_name, $sql, "no", "no");

		if(!$user_ = fetch_array($user)) {

			// login/password incorrect
			$error_message = Translate("Username/password incorrect, try again or contact the administrator", 1) . ".";

		} else {

			if($user_["locked"]) {
				$error_message = Translate("Your account is locked out, contact your administrator", 1) . ".";
			} else {
				// connect successful -> sets the cookie
				$session_timeout = param_extract("session_timeout");
				
				if($session_timeout != 0) {
					setcookie("bookings_user_id", $user_["user_id"], (time() + $session_timeout));
					setcookie("bookings_profile_id", $user_["profile_id"], (time() + $session_timeout));
					setcookie("bookings_language", $user_["language"], (time() + $session_timeout));
					setcookie("bookings_date_format", $user_["date_format"], (time() + $session_timeout));
				} else {
					setcookie("bookings_user_id", $user_["user_id"]);
					setcookie("bookings_profile_id", $user_["profile_id"]);
					setcookie("bookings_language", $user_["language"]);
					setcookie("bookings_date_format", $user_["date_format"]);
				}
			}
		}

		$script = "parent.document.location = \"index.php\";\n";

		break;

		case "delog": // **********************************************************************************************
		// wipes the cookies
		setcookie ("bookings_user_id", "", time() - 3600);
		setcookie ("bookings_profile_id", "", time() - 3600);
		setcookie ("bookings_language", "", time() - 3600);
		setcookie ("bookings_date_format", "", time() - 3600);

		$script = "top.location = \"index.php\";\n";

		break;

		case "register": // *********************************************************************************

		$validation_error = "";

		$first_name_ = validateInput("First name", $_POST["first_name"], "string", 2, 50);
		if($first_name_["error"] != "") { $validation_error .= $first_name_["error"] . "<br>"; }
		$first_name = $first_name_["input_value"];

		$last_name_ = validateInput("Last name", $_POST["last_name"], "string", 2, 50);
		if($last_name_["error"] != "") { $validation_error .= $last_name_["error"] . "<br>"; }
		$last_name = $last_name_["input_value"];

		$username_ = validateInput("Username", $_POST["username"], "string", 5, 20);
		if($username_["error"] != "") { $validation_error .= $username_["error"] . "<br>"; }
		$username = $username_["input_value"];

		$password_ = validateInput("Password", $_POST["password"], "string", 5, 20);
		if($password_["error"] != "") { $validation_error .= $password_["error"] . "<br>"; }
		$password = $password_["input_value"];

		$verify_password_ = validateInput("Password verification", $_POST["verify_password"], "string", 5, 20);
		if($verify_password_["error"] != "") { $validation_error .= $verify_password_["error"] . "<br>"; }
		$verify_password = $verify_password_["input_value"];

		if($password != $verify_password) { $validation_error .= Translate("Password and password verification don't match", 1) . "<br>"; }

		$email_ = validateInput("Email", $_POST["email"], "email", 8, 80);
		if($email_["error"] != "") { $validation_error .= $email_["error"] . "<br>"; }
		$email = $email_["input_value"];

		if($validation_error != "") {

			// check for already used username
			$sql = "SELECT user_id FROM rs_data_users WHERE login = '" . toDb($_POST["username"]) . "';";
			$temp = db_query($database_name, $sql, "no", "no");

			if($temp_ = fetch_array($temp)) {
				$validation_error .= str_replace("%u", toPage($_POST["username"], "string", ""), Translate("Username %u is already used, please choose another one.", 1)) . "<br>";
			}
		}

		if($validation_error != "") {
			$script .= "parent.document.getElementById(\"error_message\").innerHTML = \"" . toPage($validation_error, "string", "Registration form error") . "\";\n";
		} else {

			// no fields validation errors

			$rand_id = mt_rand(1, 65535);

			$sql  = "INSERT INTO rs_data_users ( rand_id, last_name, first_name, login, profile_id, email, password, locked, language, date_format, user_timezone ) VALUES ( ";
			$sql .= $rand_id . ", ";
			$sql .= "'" . toDb($_POST["last_name"]) . "', ";
			$sql .= "'" . toDb($_POST["first_name"]) . "', ";
			$sql .= "'" . toDb($_POST["username"]) . "', ";
			$sql .= "3, "; // standard user profile
			$sql .= "'" . toDb($_POST["email"]) . "', ";
			$sql .= "'" . toDb($_POST["password"]) . "', ";

			$self_registration_mode = param_extract("self_registration_mode");
			if($self_registration_mode == "no_validation") { $sql .= "0, "; } else { $sql .= "1, "; }

			$sql .= "'" . toDb(param_extract("language")) . "', ";
			$sql .= "'" . toDb(param_extract("default_date_format")) . "', ";
			$sql .= "'" . toDb(param_extract("default_user_timezone")) . "' );";
			db_query($database_name, $sql, "yes", "no");

			// sends an email with the validation code
			if($self_registration_mode == "email_validation") {

				$script = "";

				//$app_title = param_extract("app_title"); // Déjà extrait dans functions.php (inclu ci-dessus)
				$admin_email = param_extract("admin_email");

				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= "From: " . toPage($app_title, "string", "") . " <" . toPage($admin_email, "string", "") . ">\r\n";

				$message = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
				$message .= "<html>\n";
				$message .= "<head>\n";
				$message .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";
				$message .= "<title>email confirmation</title>\n";

				$message .= "<style type=\"text/css\">\n";
				$message .= "body { background:#" . toPage(param_extract("background_color"), "hex", "") . "; }\n";
				$message .= "a:link {color:black; text-decoration: none; }\n";
				$message .= "a:visited {color:black; text-decoration: none; }\n";
				$message .= "a:hover {color:red; text-decoration: none; }\n";
				$message .= "table { border-collapse: collapse; }\n";
				$message .= "td { padding: 3px; }\n";
				$message .= "</style>\n";

				$message .= "</head>\n";

				$message .= "<body>\n";

				$message .= toPage(Translate("Here is the code to finish you registration procedure" , 1), "string", "") . " : " . toPage($rand_id, "int", "") . "\n";
				$message .= "<br><br>\n";
				$message .= toPage(Translate("Please, go back to the authentication page and log in using your username, password AND registration code"), "string", "") . ".";

				$message .= "</body>\n";
				$message .= "</html>";

				mail(toPage($_POST["email"], "email", ""), toPage(Translate("Registration code", 1), "int", ""), $message, $headers);

				$script .= "parent.document.getElementById(\"notice\").innerHTML = \"";
				$script .= "<span class=\"big_text\">" . toPage(Translate("Registration successful !", 1), "string", "") . "</span>";
				$script .= "<br><br><br>";
				$script .= toPage(Translate("Your account has been created but is locked", 1), "string", "") . ". ";
				$script .= toPage(Translate("Here is what to do to unlock it :", 1), "string", "");
				$script .= "<br><br><ul>";
				$script .= "<li>" . toPage(Translate("Check your mailbox to get the registration code that was sent to you", 1), "string", "") . ".";
				$script .= "<br><br>";
				$script .= "<li>" . toPage(Translate("Come back to this page and fill in the login form", 1), "string", "") . " <u>" . toPage(Translate("including this code", 1), "string", "") . "</u>.";
				$script .= "</ul>";
				$script .= "<br>";
				$script .= "<center><button type=\\\"button\\\" onClick=\\\"document.getElementById('notice').style.visibility='hidden'\\\">" . toPage(Translate("Close", 1), "string", "") . "</button></center>";
				$script .= "\";\n";
				$script .= "parent.document.getElementById(\"notice\").style.visibility = 'visible';\n";
			}
		}

		break;

		case "delete_booking":

		$get_book_id = validateInput("get_book_id", $_GET["book_id"], "int", 0, 0);
		$get_object_id = validateInput("get_object_id", $_GET["object_id"], "int", 0, 0);

		$sql = "delete from rs_data_bookings WHERE book_id = " . toDb($get_book_id["input_value"]) . " AND object_id = " . toDb($get_object_id["input_value"]) . ";";
		db_query($database_name, $sql, "no", "no");

		break;

		case "confirm_booking": // ***********************************************************************************

		$get_validated = validateInput("", $_GET["validated"], "string", 2, 3);

		$get_book_id = validateInput("get_book_id", $_GET["book_id"], "int", 0, 0);
		$get_object_id = validateInput("get_object_id", $_GET["object_id"], "int", 0, 0);

		switch($get_validated["input_value"]) {

			case "yes":
				$text1 = toPage(Translate("has valided your booking request", 1), "string", "");
				$text2 = toPage(Translate("Validated booking request", 1), "string", "");
				$action_sql = "UPDATE rs_data_bookings SET validated = 1 WHERE book_id = " . toDb($get_book_id["input_value"]) . ";";
			break;

			case "no":
				$text1 = toPage(Translate("has refused your booking request", 1), "string", "");
				$text2 = toPage(Translate("Refused booking request", 1), "string", "");
				$action_sql = "DELETE FROM rs_data_bookings WHERE book_id = " . toDb($get_object_id["input_value"]) . ";";
		}

		// extracts booking infos
		$sql = "SELECT user_id, object_id, book_start, book_end FROM rs_data_bookings WHERE book_id = " . toDb($get_book_id["input_value"]) . ";";
		$temp = db_query($database_name, $sql, "no", "no");

		if($temp) { // booking still exists

			$temp_ = fetch_array($temp);

			$booker_id = $temp_["user_id"]; $object_id = $temp_["object_id"]; $booking_start = $temp_["book_start"]; $booking_end = $temp_["book_end"];

			// extracts object infos
			$sql = "SELECT object_name, manager_id, email_bookings FROM rs_data_objects WHERE object_id = " . toDb($object_id) . ";";
			$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
			$object_name = $temp_["object_name"]; $manager_id = $temp_["manager_id"]; $email_bookings = $temp_["email_bookings"];

			// extracts manager name
			$sql = "SELECT first_name, last_name, email FROM rs_data_users WHERE user_id = " . toDb($manager_id) . ";";
			$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
			$manager_name = $temp_["first_name"] . " " . $temp_["last_name"]; $manager_email = $temp_["email"];

			// extracts booker email
			$sql = "SELECT first_name, last_name, email FROM rs_data_users WHERE user_id = " . toDb($booker_id) . ";";
			$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
			$booker_name = $temp_["first_name"] . " " . $temp_["last_name"]; $booker_email = $temp_["email"];

			// do the action (confirm or cancel)
			db_query($database_name, $action_sql, "no", "no");

			// sends a confirmation email to the booker
			if($booker_email != "" && !is_null($booker_email) && $email_bookings == "yes") {

				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= "From: " . toPage($manager_name, "string", "") . " <" . toPage($manager_email, "email", "") . ">\r\n";

				$message = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
				$message .= "<html>\n";
				$message .= "<head>\n";
				$message .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";
				$message .= "<title>iframe</title>\n";

				$message .= "<style type=\"text/css\">\n";
				$message .= "a:link {color:black; text-decoration: none; }\n";
				$message .= "a:visited {color:black; text-decoration: none; }\n";
				$message .= "a:hover {color:red; text-decoration: none; }\n";
				$message .= "table { border-collapse: collapse; }\n";
				$message .= "td { padding: 3px; }\n";
				$message .= "</style>\n";

				$message .= "</head>\n";

				$message .= "<body>\n";

				$message .= toPage($manager_name, "string", "") . " " . toPage($text1, "string", "") . " :\n";
				$message .= "<p>\n";
				$message .= toPage(Translate("Object", 1), "string", "") . " : " . toPage($object_name, "string", "") . "<br>\n";
				$message .= toPage(Translate("Start", 1), "string", "") . " : " . date($date_format . " H:i", strtotime($booking_start)) . "<br>\n";
				$message .= toPage(Translate("End", 1), "string", "") . " : " . date($date_format . " H:i", strtotime($booking_end)) . "<p>\n";

				$message .= "</body>\n";
				$message .= "</html>";

				mail($booker_email, $text2, $message, $headers);

				$message = toPage(Translate("Confirmation was sent to", 1), "string", "") . " " . $booker_name;
			}

		} else { // booking was already cancelled

			$message = toPage(Translate("This booking was cancelled by the user even before your try to confirm it", 1), "string", "") . ".";
		}

		break;

		case "update_localization": // ****************************************************************************

		$post_localize_to = validateInput("post_localize_to", $_POST["localize_to"], "string", 4, 15);
		$post_lang_id = validateInput("post_lang_id", $_POST["lang_id"], "int", 0, 0);
		$post_localize_to_lang_id = validateInput("post_localize_to_lang_id", $_POST["$post_localize_to_" . $post_lang_id], "string", 0, 0);

		$sql = "UPDATE rs_param_lang SET ";
		$sql .= toDb($post_localize_to) . " = '" . toDb($post_localize_to_lang_id) . "' ";
		$sql .= "WHERE lang_id = " . toDb($post_lang_id) . ";";
		db_query($database_name, $sql, "no", "no");
		break;

		case "show_first_availability": // ****************************************************************************

			$get_object_id = validateInput("get_object_id", $_GET["object_id"], "int", 0, 0);
			$get_start_date = validateInput("get_start_date", $_GET["start_date"], "date", 0, 0);
			$get_start_hour = validateInput("get_start_hour", $_GET["start_hour"], "string", 0, 5); // 00:00
			$get_duration = validateInput("get_duration", $_GET["duration"], "int", 0, 0);


			$start_date = DateReformat($get_start_date) . " " . $get_start_hour . ":00";

			$first_availability = getAvailability($get_object_id, $start_date, $get_duration);

			$script = "parent.document.getElementById(\"slot_display\").innerHTML = \"" . $first_availability . "\";\n";

		break;

		case "export_localization": // ****************************************************************************

			$absolute_csvfile_path = dirname($_SERVER["SCRIPT_FILENAME"]) . "/openbookings_localization.csv";

			$file_handle = fopen($absolute_csvfile_path, "w");

			fwrite($file_handle, "THIS IS AN OPENBOOKINGS.ORG " . param_extract("app_version") . " LOCALIZATION EXCHANGE FILE (see http://www.openbookings.org for informations).\n");

			$columns_list_sql = ""; $columns_array = array();

			$sql = "SHOW COLUMNS FROM rs_param_lang";
			$columns = db_query($database_name, $sql, "no", "no");

			$line = ""; while($columns_ = fetch_array($columns)) {
				if($columns_["Field"] != "lang_id") {
					$columns_array[] = $columns_["Field"];
					$line .= "\"" . $columns_["Field"] . "\";";
				}
			}

			fwrite($file_handle, substr($line, 0, -1) . "\n");

			$sql = "SELECT " . implode(",", $columns_array) . " FROM rs_param_lang;";
			$localization = db_query($database_name, $sql, "no", "no");


			while($localization_ = fetch_array($localization)) {

				$line = ""; foreach($columns_array as $column_name) { $line .= "\"" . $localization_[$column_name] . "\";"; }
				$line = substr($line, 0, -1);

				fwrite($file_handle, $line . "\n");
			}

			fclose($file_handle);

			$script = "document.location = \"openbookings_localization.csv\";\n";

		break;

		case "import_localization": // ****************************************************************************

		// get the absolute path app root folder
		$app_root_path = dirname($_SERVER["SCRIPT_FILENAME"]);

		// get the name of the uploaded file
		$file_name = basename($_FILES["localization_file"]["name"]);

		// move uploaded file to app root folder if file extension is .csv
		if(substr($file_name, strlen($file_name)-4) == ".csv") {
			move_uploaded_file($_FILES["localization_file"]["tmp_name"], $app_root_path . "/" . $file_name);
		}

		// open uploaded file in read mode
		$file_handle = fopen($app_root_path . "/" . $file_name, "r");

		if($file_handle) {

			// ensures there is no temp localization table left
			$sql = "DROP TABLE IF EXISTS rs_temp_lang";
			db_query($database_name, $sql, "no", "no");

			// skip first line (reserved for file informations)
			$buffer = fgets($file_handle);

			// gets columns/languages names
			$buffer = fgets($file_handle);

			// constructs temp table structure according to columns found in csv file
			$sql  = "CREATE TABLE rs_temp_lang ( ";
			$sql .= "lang_id mediumint(8) unsigned NOT NULL auto_increment, ";

			$buffer = str_replace("\"", "", $buffer); // removes "
			$array_columns = explode(";", $buffer);

			foreach($array_columns as $column_name) { $sql .= toDb($column_name) . " varchar(255) default NULL, "; }
			$sql = substr($sql, 0, -1); // removes last comma (,)

			$sql .= "PRIMARY KEY (lang_id), ";

			foreach($array_columns as $column_name) { $sql .= "KEY " . toDb($column_name) . " (" . toDb($column_name) . "),"; }
			$sql = substr($sql, 0, -1); // removes last comma (,)

			$sql .= " ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

			db_query($database_name, $sql, "no", "no");

			while (!feof($file_handle)) { // fills temp table with csv file data

				$buffer = fgets($file_handle);
				$array_values = explode(";", $buffer);

				$sql  = "INSERT INTO rs_temp_lang ( ";
				foreach($array_columns as $column_name) { $sql .= toDb($column_name) . ","; }
				$sql = substr($sql, 0, -1); // removes last comma (,)
				$sql .= " ) VALUES ( ";
				foreach($array_values as $value) { $sql .= toDb($value) . ","; }
				$sql = substr($sql, 0, -1); // removes last comma (,)
				$sql .= ");";

				db_query($database_name, $sql, "no", "no");
			}

			fclose($file_handle);

			// replace localization_table (rs_param_lang) with temp table
			$sql = "RENAME TABLE rs_param_lang TO rs_backup_lang;";
			db_query($database_name, $sql, "no", "no");

			$sql = "RENAME TABLE rs_temp_lang TO rs_param_lang;";
			db_query($database_name, $sql, "no", "no");

			$sql = "DROP TABLE rs_backup_lang;";
			db_query($database_name, $sql, "no", "no");

			$sql = "DROP TABLE rs_temp_lang";
			db_query($database_name, $sql, "no", "no");

			$script  = "parent.document.location = \"localize.php\";\n";
			$script .= "alert(\"" . toPage(Translate("Import Successful", 1), "string", "") . "\");\n";
		}

	} // end switch

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo toPage($app_title, "string", "") . " :: " . toPage(Translate("Actions", 1), "string", ""); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
<?php echo $script; ?>
--></script>

</head>

<body>

<?php //echo $message; ?>

</body>

</html>
