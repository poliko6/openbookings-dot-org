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

	switch($_REQUEST["action_"]) {

		case "connect": // **********************************************************************************************

		$sql  = "SELECT user_id, profile_id, locked, language, date_format, user_timezone FROM rs_data_users ";
		$sql  .= "WHERE login = '" . sqlInjectShield($_REQUEST["username"]) . "' ";
		$sql .= "AND password = '" . sqlInjectShield($_REQUEST["password"]) . "' ";
		$sql .= "AND profile_id >= " . $application_access_level . ";";
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
					setcookie("bookings_time_offset", ($user_["user_timezone"] - param_extract("server_timezone")), (time() + $session_timeout));
					setcookie("bookings_date_format", $user_["date_format"], (time() + $session_timeout));
				} else {
					setcookie("bookings_user_id", $user_["user_id"]);
					setcookie("bookings_profile_id", $user_["profile_id"]);
					setcookie("bookings_language", $user_["language"]);
					setcookie("bookings_time_offset", ($user_["user_timezone"] - param_extract("server_timezone")));
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
		setcookie ("bookings_time_offset", "", time() - 3600);
		setcookie ("bookings_date_format", "", time() - 3600);

		$script = "top.location = \"index.php\";\n";

		break;

		case "register": // *********************************************************************************

		$form_fields = array(); $validation_error = false;

		$form_fields["first_name"] = array("alphanum", 1, Translate("First name", 1));
		$form_fields["last_name"] = array("alphanum", 1, Translate("Last name", 1));
		$form_fields["username"] = array("alphanum", 3, Translate("Username", 1));
		$form_fields["password"] = array("alphanum", 6, Translate("Password", 1));
		$form_fields["verify_password"] = array("alphanum", "password", Translate("Verify password", 1));
		$form_fields["email"] = array("email", 1, Translate("Email", 1));

		$errors_array = checkForm($form_fields, $_POST); // gets errors in an array

		$html = "";

		foreach($errors_array as $error) {

			if($error[1] != "") {
				$validation_error = true;
				$html .= "- " . $error[0] . " " . $error[1] . "<br>";
			}
		}

		$script .= "parent.document.getElementById(\"error_message\").innerHTML = \"" . $html . "\";\n";

		if(!$validation_error) {

			// check for already used username
			$sql = "SELECT user_id FROM rs_data_users WHERE login = '" . addslashes($_REQUEST["new_username"]) . "';";
			$temp = db_query($database_name, $sql, "no", "no");

			if($temp_ = fetch_array($temp)) {

				$validation_error = true;

				$script .= "parent.document.getElementById(\"new_username_info\").innerHTML = \"";
				$script .= "<table class='error_info'><tr>";
				$script .= "<th><img src='pictures/red_triangle.png'></th>";
				$script .= "<td>" . Translate("already used", 1) . "</td>";
				$script .= "</tr></table>\";\n";
				$script .= "parent.document.getElementById(\"new_username_info\").style.visibility = 'visible';\n";
			}
		}

		if(!$validation_error) { // no fields validation errors

			$rand_id = mt_rand(1, 65535);

			$sql  = "INSERT INTO rs_data_users ( rand_id, last_name, first_name, login, profile_id, email, password, locked, language, date_format, user_timezone ) VALUES ( ";
			$sql .= $rand_id . ", ";
			$sql .= "'" . addslashes($_REQUEST["last_name"]) . "', ";
			$sql .= "'" . addslashes($_REQUEST["first_name"]) . "', ";
			$sql .= "'" . addslashes($_REQUEST["new_username"]) . "', ";
			$sql .= "3, "; // = user
			$sql .= "'" . addslashes($_REQUEST["new_email"]) . "', ";
			$sql .= "'" . addslashes($_REQUEST["new_password"]) . "', ";

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
				$headers .= "From: " . $app_title . " <" . $admin_email . ">\r\n";

				$message = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">" . chr(10);
				$message .= "<html>" . chr(10);
				$message .= "<head>" . chr(10);
				$message .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">" . chr(10);
				$message .= "<title>email confirmation</title>" . chr(10);

				$message .= "<style type=\"text/css\">" . chr(10);
				$message .= "body { background:#" . param_extract("background_color") . "; }" . chr(10);
				$message .= "a:link {color:black; text-decoration: none; }" . chr(10);
				$message .= "a:visited {color:black; text-decoration: none; }" . chr(10);
				$message .= "a:hover {color:red; text-decoration: none; }" . chr(10);
				$message .= "table { border-collapse: collapse; }" . chr(10);
				$message .= "td { padding: 3px; }" . chr(10);
				$message .= "</style>" . chr(10);

				$message .= "</head>" . chr(10);

				$message .= "<body>" . chr(10);

				$message .= Translate("Here is the code to finish you registration procedure" , 1) . " : " . $rand_id . chr(10);
				$message .= "<br><br>" . chr(10);
				$message .= Translate("Please, go back to the authentication page and log in using your username, password AND registration code") . ".";

				$message .= "</body>" . chr(10);
				$message .= "</html>";

				mail($_REQUEST["email"], Translate("Registration code", 1), $message, $headers);

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

		case "add_booking": // ****************************************************************************************

		// the function AddBooking() is implemented in the file "functions.php"
		AddBooking($_REQUEST["manager_email"], $_REQUEST["booker_id"], $_REQUEST["object_id"], $_REQUEST["booking_start"], $_REQUEST["booking_end"], $_REQUEST["misc_info"], $_REQUEST["validated"]);
		break;

		case "confirm_booking": // ***********************************************************************************

		switch($_REQUEST["validated"]) {

			case "yes":
				$text1 = Translate("has valided your booking request", 1);
				$text2 = Translate("Validated booking request", 1);
				$action_sql = "UPDATE rs_data_bookings SET validated = 1 WHERE book_id = " . $_REQUEST["book_id"] . ";";
			break;

			case "no":
				$text1 = Translate("has refused your booking request", 1);
				$text2 = Translate("Refused booking request", 1);
				$action_sql = "DELETE FROM rs_data_bookings WHERE book_id = " . $_REQUEST["book_id"] . ";";
		}

		// extracts booking infos
		$sql = "SELECT user_id, object_id, book_start, book_end FROM rs_data_bookings WHERE book_id = " . $_REQUEST["book_id"] . ";";
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
			$booker_name = $temp_["first_name"] . " " . $temp_["last_name"]; $booker_email = $temp_["email"];

			// do the action (confirm or cancel)
			db_query($database_name, $action_sql, "no", "no");

			// sends a confirmation email to the booker
			if($booker_email != "" && !is_null($booker_email) && $email_bookings == "yes") {

				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= "From: " . $manager_name . " <" . $manager_email . ">\r\n";

				$message = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">" . chr(10);
				$message .= "<html>" . chr(10);
				$message .= "<head>" . chr(10);
				$message .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">" . chr(10);
				$message .= "<title>iframe</title>" . chr(10);

				$message .= "<style type=\"text/css\">" . chr(10);
				$message .= "a:link {color:black; text-decoration: none; }" . chr(10);
				$message .= "a:visited {color:black; text-decoration: none; }" . chr(10);
				$message .= "a:hover {color:red; text-decoration: none; }" . chr(10);
				$message .= "table { border-collapse: collapse; }" . chr(10);
				$message .= "td { padding: 3px; }" . chr(10);
				$message .= "</style>" . chr(10);

				$message .= "</head>" . chr(10);

				$message .= "<body>" . chr(10);

				$message .= $manager_name . " " . $text1 . " :" . chr(10);
				$message .= "<p>" . chr(10);
				$message .= Translate("Object", 1) . " : " . $object_name . "<br>" . chr(10);
				$message .= Translate("Start", 1) . " : " . date($date_format . " H:i", strtotime($booking_start)) . "<br>" . chr(10);
				$message .= Translate("End", 1) . " : " . date($date_format . " H:i", strtotime($booking_end)) . "<p>" . chr(10);

				$message .= "</body>" . chr(10);
				$message .= "</html>";

				mail($booker_email, $text2, $message, $headers);

				$message = Translate("Confirmation was sent to", 1) . " " . $booker_name;
			}

		} else { // booking was already cancelled

			$message = Translate("This booking was cancelled by the user even before your try to confirm it", 1) . ".";
		}

		break;

		case "update_localization":
		$sql = "UPDATE rs_param_lang SET ";
		$sql .= $_REQUEST["localize_to"] . " = '" . addslashes($_REQUEST["localize_to_" . $_REQUEST["lang_id"]]) . "' ";
		$sql .= "WHERE lang_id = " . $_REQUEST["lang_id"] . ";";
		db_query($database_name, $sql, "no", "no");
		break;

		case "show_available_slots": // ****************************************************************************

		// $_GET["object_id"], $_GET["start_date"], $_GET["start_hour"], $_GET["book_duration"]

		global $date_format;

		$booking_start = DateAndHour(DateReformat($_GET["start_date"]), date("H:i", $_GET["start_hour"]));

		$timestamp_duration = strtotime("1970-01-01 " . $_GET["book_duration"]); // duration in seconds

		$availables_slots_list  = "<table class=\\\"list_table\\\" summary=\\\"\\\" style=\\\"width:100%\\\"><tr>";
		$availables_slots_list .= "<th>" . Translate("Start", 1) . "</th>";
		$availables_slots_list .= "<th>" . Translate("End", 1) . "</th>";
		$availables_slots_list .= "<th>" . Translate("Choice", 1) . "</th>";
		$availables_slots_list .= "</tr>";

		// extract first available slot for specified start date, start hour and duration
		$sql  = "SELECT book_id, book_start, book_end FROM rs_data_bookings ";
		$sql .= "WHERE object_id = " . $_GET["object_id"] . " ";
		$sql .= "AND book_end >= '" . $booking_start . "' ";
		$sql .= "ORDER BY book_end;";
		$temp = db_query($database_name, $sql, "no", "no");

		$n_slot = 1;

		if(num_rows($temp)) {

			// Create temporary table to store Space Between Bookings (Free Slots)
			$Sql  = "CREATE TEMPORARY TABLE `rs_data_nobookings` ( ";
			$Sql .= "`n` SMALLINT(5) UNSIGNED NOT NULL, ";
			$Sql .= "`book_end_id` INT(10) UNSIGNED NOT NULL DEFAULT '0', ";
			$Sql .= "`book_end` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ";
			$Sql .= "`book_start_id` INT(10) UNSIGNED NOT NULL DEFAULT '0', ";
			$Sql .= "`book_start` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00', ";
			$Sql .= "PRIMARY KEY (`n`), ";
			$Sql .= "KEY `book_end` (`book_end`), ";
			$Sql .= "KEY `book_start` (`book_start`) ";
			$Sql .= ") TYPE=MYISAM AUTO_INCREMENT=1 ;";
			db_query($database_name, $sql, "No", "No");

			$nobookings_array = array();

			$n = 0; $nobookings_array[$n]= array();

			// creates an array with available slots
			while($temp_ = fetch_array($temp)) { $n++;

				$nobookings_array[$n] = array();

				$nobookings_array[$n]["end_id"] = $temp["book_id"];
				$nobookings_array[$n-1]["start_id"] = $temp["book_id"];
				$nobookings_array[$n-1]["start"] = $temp["book_start"];
				$nobookings_array[$n]["end"] = $temp["book_end"];
			}

			// populates temporary table with data from previous array
			foreach($nobookings_array as $nobookings_line) {
				$sql  = "INSERT INTO rs_data_nobookings ( book_end_id, book_end, book_start_id, book_start ) VALUES ( ";
				$sql .= $nobookings_line["end_id"] . ", ";
				$sql .= "'" . $nobookings_line["end"] . "', ";
				$sql .= $nobookings_line["start_id"] . ", ";
				$sql .= "'" . $nobookings_line["start"] . "' );";
				db_query($database_name, $sql, "no", "no");
			}

			// extracts availables slot according to new booking's desired duration
			$sql  = "SELECT book_end AS slot_start, book_start AS slot_end ";
			$sql .= "FROM rs_data_nobookings ";
			$sql .= "WHERE (UNIX_TIMESTAMP(book_end) - UNIX_TIMESTAMP(book_start)) >= " . $timestamp_duration . " ";
			$sql .= "ORDER BY book_end;";
			$availables_slots = db_query($database_name, $sql, "no", "no");

			while($availables_slots_ = fetch_array($availables_slots)) {
				$availables_slots_list .= "<tr>";
				$availables_slots_list .= "<td>" . $availables_slots_["slot_start"] . "</td>";
				$availables_slots_list .= "<td>" . $availables_slots_["slot_end"] . "</td>";
				$availables_slots_list .= "<td>";
				$availables_slots_list .= "<input type=\\\"radio\\\" name=\\\"slot_choice\\\" value=\"" . $n_slot . "\">";
				$availables_slots_list .= "<input type=\\\"hidden\\\" id=\\\"start_date_" . $n_slot . "\\\" value=\\\"" . date($date_format, $availables_slots_["slot_start"]) . "</td>";
				$availables_slots_list .= "</tr>";

				$n_slot++;
			}

			$availables_slots_list .= "</table>";

		} else {

			// full time available
			$availables_slots_list .= "<tr>";
			$availables_slots_list .= "<td>" . date($date_format . " H:i", strtotime($booking_start)) . "</td>";
			$availables_slots_list .= "<td>" . Translate("No limit", 1) . "</td>";
			$availables_slots_list .= "<td><input type=\\\"radio\\\" name=\\\"slot_choice\\\" value=\\\"1\\\" checked>";
			$availables_slots_list .= "<input type=\\\"hidden\\\" id=\\\"start_date_" . $n_slot . "\\\" value=\\\"" . date($date_format, $availables_slots_["slot_start"]) . "</td>";
			$availables_slots_list .= "</tr></table>";
		}

		$script = "parent.document.getElementById(\"available_slots\").innerHTML = \"" . $availables_slots_list . "\";\n";

		case "export_localization": // ****************************************************************************

			$absolute_csvfile_path = dirname($_SERVER["SCRIPT_FILENAME"]) . "/openbookings_localization.csv";

			$handle = fopen($absolute_csvfile_path, "w");

			fwrite($handle, "THIS IS AN OPENBOOKINGS.ORG " . param_extract("app_version") . " LOCALIZATION EXCHANGE FILE (see http://www.openbookings.org for informations).\n");

			$columns_list_sql = ""; $columns_array = array();

			$sql = "SHOW COLUMNS FROM rs_param_lang";
			$columns = db_query($database_name, $sql, "no", "no");

			$line = ""; while($columns_ = fetch_array($columns)) {
				if($columns_["Field"] != "lang_id") {
					$columns_array[] = $columns_["Field"];
					$line .= "\"" . $columns_["Field"] . "\";";
				}
			}

			fwrite($handle, substr($line, 0, -1) . "\n");

			$sql = "SELECT " . implode(",", $columns_array) . " FROM rs_param_lang;";
			$localization = db_query($database_name, $sql, "no", "no");


			while($localization_ = fetch_array($localization)) {

				$line = ""; foreach($columns_array as $column_name) {
					$line .= "\"" . $localization_[$column_name] . "\";";
				}

				fwrite($handle, $line . "\n");
			}

			fclose($handle);

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
		$handle = fopen($app_root_path . "/" . $file_name, "r");

		if($handle) {

			// ensures there is no temp localization table left
			$sql = "DROP TABLE IF EXISTS rs_temp_lang";
			db_query($database_name, $sql, "no", "no");

			// skip first line (reserved for file informations)
			$buffer = fgets($handle);

			// gets columns/languages names
			$buffer = fgets($handle);

			// constructs temp table structure according to columns found in csv file
			$sql  = "CREATE TABLE rs_temp_lang ( ";
			$sql .= "lang_id mediumint(8) unsigned NOT NULL auto_increment, ";

			$buffer = str_replace("\"", "", $buffer); // removes "
			$array_buffer = explode(",", $buffer);

			foreach($array_buffer as $column_name) {
				$sql .= $column_name . " varchar(255) default NULL, ";
			}

			$sql .= "PRIMARY KEY (lang_id), ";

			foreach($array_buffer as $column_name) {
				$sql .= "KEY " . $column_name . " (" . $column_name . ") ";
			}

			$sql .= " ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

			db_query($database_name, $sql, "no", "no");

			while (!feof($handle)) { // fills temp table with csv file content

				$buffer = fgets($handle);
				$array_buffer = explode(",", $buffer);

				$sql  = "INSERT INTO rs_temp_lang ( "
				foreach($array_buffer as $column_name) { $sql .= $column_name . ","; }
				$sql .= substr($sql, 0, -1) // removes last comma (,)
				$sql .= " ) VALUES ( ";
				foreach($array_buffer as $column_value) { $sql .= $column_value . ","; }
				$sql .= substr($sql, 0, -1) // removes last comma (,)
				$sql .= ");";

				db_query($database_name, $sql, "no", "no");
			}

			fclose($handle);
		}

	} // end switch

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title><?php echo $app_title . " :: " . Translate("Actions", 1); ?></title>

<link rel="stylesheet" type="text/css" href="styles.php">

<script type="text/javascript"><!--
<?php echo $script; ?>
--></script>

</head>

<body>

<?php echo $message; ?>

</body>

</html>
