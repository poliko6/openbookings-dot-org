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

	switch($_POST["action_"]) {

		case "connect": // **********************************************************************************************

		$error_message = "";

		$post_username = checkVar("sql", $_POST["username"], "string", "5", "20", "", "username");
		$post_password = checkVar("sql", $_POST["username"], "string", "5", "20", "", "password");

		$error_message .= (!$post_username["ok"])?$post_username["error"] . "<br>":"";
		$error_message .= (!$post_password["ok"])?$post_password["error"] . "<br>":"";

		if($post_username["ok"] && $post_password["ok"]) {

			$sql  = "SELECT user_id, profile_id, locked, language, date_format, user_timezone FROM rs_data_users ";
			$sql .= "WHERE login = '" . $post_username["value"] . "' ";
			$sql .= "AND password = '" . $post_password["value"] . "' ";
			$sql .= "AND profile_id >= " . $application_access_level . ";";
			$user = db_query($database_name, $sql, "no", "no");

			if(!$user_ = fetch_array($user)) {

				// login/password incorrect
				$error_message .= Translate("Username/password incorrect, try again or contact the administrator", 1) . ".<br>";

			} else {

				if($user_["locked"]) {
					$error_message = Translate("Your account is locked out, contact your administrator", 1) . ".<br>";
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
		}

		if($error_message == "") {
			$script = "parent.document.location = \"index.php\";\n";
		} else {
			$script = "parent.document.getElementById(\"error_message\").innerHTML = \"" . $error_message . "\";\n";
		}

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

		$first_name_ = checkVar("", $_POST["first_name"], "string", 2, 50, "", "First name");
		if(!$first_name_["ok"]) { $validation_error .= $first_name_["error"] . "<br>"; }
		$first_name = $first_name_["value"];

		$last_name_ = checkVar("", $_POST["last_name"], "string", 2, 50, "", "Last name");
		if(!$last_name_["ok"]) { $validation_error .= $last_name_["error"] . "<br>"; }
		$last_name = $last_name_["value"];

		$username_ = checkVar("", $_POST["username"], "string", 5, 20, "", "Username");
		if(!$username_["ok"]) { $validation_error .= $username_["error"] . "<br>"; }
		$username = $username_["value"];

		$password_ = checkVar("", $_POST["password"], "string", 5, 20, "", "Password");
		if(!$password_["ok"]) { $validation_error .= $password_["error"] . "<br>"; }
		$password = $password_["value"];

		$verify_password_ = checkVar("", $_POST["verify_password"], "string", 5, 20, "", "Password verification");
		if(!$verify_password_["ok"]) { $validation_error .= $verify_password_["error"] . "<br>"; }
		$verify_password = $verify_password_["value"];

		if($password != $verify_password) { $validation_error .= Translate("Password and password verification don't match", 1) . "<br>"; }

		$email_ = checkVar("", $_POST["email"], "email", 8, 80, "", "Email");
		if(!$email_["ok"]) { $validation_error .= $email_["error"] . "<br>"; }
		$email = $email_["value"];

		if($validation_error != "") {

			// check for already used username
			$sql = "SELECT user_id FROM rs_data_users WHERE login = '" . checkVar("sql", $_POST["username"], "", "", "", "", "") . "';";
			$temp = db_query($database_name, $sql, "no", "no");

			if($temp_ = fetch_array($temp)) {

				$validation_error = sprintf(Translate("Username %1\$s is already used, please choose another one.", 1), checkVar("html", $_POST["username"], "", "", "", "", "")) . "<br>";
			}
		}

		if($validation_error != "") {
			$script .= "parent.document.getElementById(\"error_message\").innerHTML = \"" . $validation_error . "\";\n";
		} else {

			// no fields validation errors

			$rand_id = mt_rand(1, 65535);

			$sql  = "INSERT INTO rs_data_users ( rand_id, last_name, first_name, login, profile_id, email, password, locked, language, date_format, user_timezone ) VALUES ( ";
			$sql .= $rand_id . ", ";
			$sql .= "'" . checkVar("sql", $_POST["last_name"], "", "", "", "", "") . "', ";
			$sql .= "'" . checkVar("sql", $_POST["first_name"], "", "", "", "", "") . "', ";
			$sql .= "'" . checkVar("sql", $_POST["username"], "", "", "", "", "") . "', ";
			$sql .= "3, "; // standard user profile
			$sql .= "'" . checkVar("sql", $_POST["email"], "", "", "", "", "") . "', ";
			$sql .= "'" . checkVar("sql", $_POST["password"], "", "", "", "", "") . "', ";

			$self_registration_mode = param_extract("self_registration_mode");
			if($self_registration_mode == "no_validation") { $sql .= "0, "; } else { $sql .= "1, "; }

			$sql .= "'" . param_extract("language") . "', ";
			$sql .= "'" . param_extract("default_date_format") . "', ";
			$sql .= "'" . param_extract("default_user_timezone") . "' );";
			db_query($database_name, $sql, "yes", "no");

			// sends an email with the validation code
			if($self_registration_mode == "email_validation") {

				$script = "";

				//$app_title = param_extract("app_title"); // Déjà extrait dans functions.php (inclu ci-dessus)
				$admin_email = param_extract("admin_email");

				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= "From: " . checkVar("html", $app_title, "", "", "", "", "") . " <" . checkVar("html", $admin_email, "", "", "", "", "") . ">\r\n";

				$message = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
				$message .= "<html>\n";
				$message .= "<head>\n";
				$message .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";
				$message .= "<title>email confirmation</title>\n";

				$message .= "<style type=\"text/css\">\n";
				$message .= "body { background:#" . param_extract("background_color") . "; }\n";
				$message .= "a:link {color:black; text-decoration: none; }\n";
				$message .= "a:visited {color:black; text-decoration: none; }\n";
				$message .= "a:hover {color:red; text-decoration: none; }\n";
				$message .= "table { border-collapse: collapse; }\n";
				$message .= "td { padding: 3px; }\n";
				$message .= "</style>\n";

				$message .= "</head>\n";

				$message .= "<body>\n";

				$message .= Translate("Here is the code to finish you registration procedure" , 1) . " : " . $rand_id . "\n";
				$message .= "<br><br>\n";
				$message .= Translate("Please, go back to the authentication page and log in using your username, password AND registration code") . ".";

				$message .= "</body>\n";
				$message .= "</html>";

				mail(checkVar("html", $_POST["email"], "email", "", "", "", ""), Translate("Registration code", 1), $message, $headers);

				$script .= "parent.document.getElementById(\"notice\").innerHTML = \"";
				$script .= "<span class=\"big_text\">" . Translate("Registration successful !", 1) . "</span>";
				$script .= "<br><br><br>";
				$script .= Translate("Your account has been created but is locked", 1) . ". ";
				$script .= Translate("Here is what to do to unlock it :", 1);
				$script .= "<br><br><ul>";
				$script .= "<li>" . Translate("Check your mailbox to get the registration code that was sent to you", 1) . ".";
				$script .= "<br><br>";
				$script .= "<li>" . Translate("Come back to this page and fill in the login form", 1) . " <u>" . Translate("including this code", 1) . "</u>.";
				$script .= "</ul>";
				$script .= "<br>";
				$script .= "<center><button type=\\\"button\\\" onClick=\\\"document.getElementById('notice').style.visibility='hidden'\\\">" . Translate("Close", 1) . "</button></center>";
				$script .= "\";\n";
				$script .= "parent.document.getElementById(\"notice\").style.visibility = 'visible';\n";
			}
		}

		break;

		case "delete_booking":

		$get_book_id = checkVar("sql", $_GET["book_id"], "int", "", "", "", "");
		$get_object_id = checkVar("sql", $_GET["object_id"], "int", "", "", "", "");

		if($get_book_id["ok"] && $get_object_id["ok"]) {

			$sql = "delete from rs_data_bookings WHERE book_id = " . $get_book_id["value"] . " AND object_id = " . $get_object_id["value"] . ";";
			db_query($database_name, $sql, "no", "no");
		}

		break;

		case "confirm_booking": // ***********************************************************************************

		$get_validated = checkVar("html", $_GET["validated"], "string", 2, 3, "", "");
		$get_book_id = checkVar("sql", $_GET["book_id"], "int", "", "", "", "");
		$get_object_id = checkVar("sql", $_GET["object_id"], "int", "", "", "", "");

		if($get_validated["ok"] && $get_book_id["ok"] && $get_object_id["ok"]) {

			switch($get_validated["value"]) {

				case "yes":
					$text1 = Translate("has valided your booking request", 1);
					$text2 = Translate("Validated booking request", 1);
					$action_sql = "UPDATE rs_data_bookings SET validated = 1 WHERE book_id = " . $get_book_id["value"] . ";";
				break;

				case "no":
					$text1 = Translate("has refused your booking request", 1);
					$text2 = Translate("Refused booking request", 1);
					$action_sql = "DELETE FROM rs_data_bookings WHERE book_id = " . $get_object_id["value"] . ";";
			}

			// extracts booking infos
			$sql = "SELECT user_id, object_id, book_start, book_end FROM rs_data_bookings WHERE book_id = " . $get_book_id["value"] . ";";
			$temp = db_query($database_name, $sql, "no", "no");

			if($temp) { // booking still exists

				$temp_ = fetch_array($temp);

				$booker_id = $temp_["user_id"]; $object_id = $temp_["object_id"]; $booking_start = $temp_["book_start"]; $booking_end = $temp_["book_end"];

				// extracts object infos
				$sql = "SELECT object_name, manager_id, email_bookings FROM rs_data_objects WHERE object_id = " . $object_id . ";";
				$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
				$object_name = $temp_["object_name"]; $manager_id = $temp_["manager_id"]; $email_bookings = $temp_["email_bookings"];

				// extracts manager name
				$sql = "SELECT first_name, last_name, email FROM rs_data_users WHERE user_id = " . $manager_id . ";";
				$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
				$manager_name = $temp_["first_name"] . " " . $temp_["last_name"]; $manager_email = $temp_["email"];

				// extracts booker email
				$sql = "SELECT first_name, last_name, email FROM rs_data_users WHERE user_id = " . $booker_id . ";";
				$temp = db_query($database_name, $sql, "no", "no"); $temp_ = fetch_array($temp);
				$booker_name = $temp_["first_name"] . " " . $temp_["last_name"];

				$booker_email = checkVar("", $temp_["email"], "email", "", "", "", "");

				// do the action (confirm or cancel)
				db_query($database_name, $action_sql, "no", "no");

				// sends a confirmation email to the booker
				if($booker_email["ok"] && $email_bookings == "yes") {

					$headers  = "MIME-Version: 1.0\r\n";
					$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
					$headers .= "From: " . checkVar("html", $manager_name, "string", "", "", "", "") . " <" . checkVar("html", $manager_email, "email", "", "", "", "") . ">\r\n";

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

					$message .= checkVar("html", $manager_name, "string", "", "", "", "") . " " . checkVar("html", $text1, "string", "", "", "", "") . " :\n";
					$message .= "<p>\n";
					$message .= Translate("Object", 1) . " : " . checkVar("html", $object_name, "string", "", "", "", "") . "<br>\n";
					$message .= Translate("Start", 1) . " : " . date($date_format . " H:i", strtotime($booking_start)) . "<br>\n";
					$message .= Translate("End", 1) . " : " . date($date_format . " H:i", strtotime($booking_end)) . "<br><br>\n";

					$message .= "</body>\n";
					$message .= "</html>";

					mail($booker_email, $text2, $message, $headers);

					$message = Translate("Confirmation was sent to", 1) . " " . $booker_name;
				}

			} else { // booking was already cancelled

				$message = Translate("This booking was cancelled by the user even before your try to confirm it", 1) . ".";
			}
		}

		break;

		case "update_localization": // ****************************************************************************

		$post_localize_to = checkVar("sql", $_POST["localize_to"], "string", 4, 15, "", "");
		$post_lang_id = checkVar("sql", $_POST["lang_id"], "int", "", "", "", "");
		$post_localize_to_lang_id = checkVar("sql", $_POST["$post_localize_to_" . $post_lang_id], "string", "", "", "", "");

		if($post_localize_to["ok"] && $post_lang_id["ok"] && $post_localize_to_lang_id["ok"]) {
			$sql = "UPDATE rs_param_lang SET ";
			$sql .= $post_localize_to["value"] . " = '" . $post_localize_to_lang_id["value"] . "' ";
			$sql .= "WHERE lang_id = " . $post_lang_id["value"] . ";";
			db_query($database_name, $sql, "no", "no");
		}

		break;

		case "show_first_availability": // ****************************************************************************

			$error_message = "";

			$get_object_id = checkVar("", $_GET["object_id"], "int", "", "", "", "Object ID");
			$get_start_date = checkVar("", $_GET["start_date"], "date", "", "", "", "Start date");
			$get_start_hour = checkVar("", $_GET["start_hour"], "hour",  "", "", "", "Start hour"); // 00:00
			$get_duration = checkVar("", $_GET["duration"], "int",  "", "", "", "Duration");

			if($get_object_id["ok"] && $get_start_date["ok"] && $get_start_hour["ok"] && $get_duration["ok"]) {

				$start_date = dateFormat($get_start_date, "", "Y-m-d") . " " . $get_start_hour . ":00";
				$first_availability = getAvailability($get_object_id, $start_date, $get_duration);
				$script = "parent.document.getElementById(\"info_display\").innerHTML = \"" . $first_availability . "\";\n";

			} else {

				// displays error to user
				$error_message .= (!$get_object_id["ok"])?$get_object_id["error"] . "<br>":"";
				$error_message .= (!$get_start_date["ok"])?$get_start_date["error"] . "<br>":"";
				$error_message .= (!$get_start_hour["ok"])?$get_start_hour["error"] . "<br>":"";
				$error_message .= (!$get_duration["ok"])?$get_duration["error"] . "<br>":"";

				$script = "parent.document.getElementById(\"info_display\").innerHTML = \"" . $error_message . "\";\n";
			}

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

			foreach($array_columns as $column_name) { $sql .= checkVar("sql", $column_name, "string", "", "", "", "") . " varchar(255) default NULL, "; }
			$sql = substr($sql, 0, -1); // removes last comma (,)

			$sql .= "PRIMARY KEY (lang_id), ";

			foreach($array_columns as $column_name) { $sql .= "KEY " . checkVar("sql", $column_name, "string", "", "", "", "") . " (" . checkVar("sql", $column_name, "string", "", "", "", "") . "),"; }
			$sql = substr($sql, 0, -1); // removes last comma (,)

			$sql .= " ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

			db_query($database_name, $sql, "no", "no");

			while (!feof($file_handle)) { // fills temp table with csv file data

				$buffer = fgets($file_handle);
				$array_values = explode(";", $buffer);

				$sql  = "INSERT INTO rs_temp_lang ( ";
				foreach($array_columns as $column_name) { $sql .= checkVar("sql", $column_name, "string", "", "", "", "") . ","; }
				$sql = substr($sql, 0, -1); // removes last comma (,)
				$sql .= " ) VALUES ( ";
				foreach($array_values as $value) { $sql .= checkVar("sql", $value, "string", "", "", "", "") . ","; }
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
			$script .= "alert(\"" . Translate("Import Successful", 1) . "\");\n";
		}

	} // end switch

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo checkVar("html", $app_title, "string", "", "", "", "") . " :: " . Translate("Actions", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
<?php echo $script; ?>
--></script>

</head>

<body>

<?php //echo $message; ?>

</body>

</html>
