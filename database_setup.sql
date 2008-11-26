-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Serveur: 127.0.0.1
-- Généré le : Mer 26 Novembre 2008 à 17:29
-- Version du serveur: 5.0.51
-- Version de PHP: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `openbookings_current`
--

-- --------------------------------------------------------

--
-- Structure de la table `rs_data_bookings`
--

CREATE TABLE IF NOT EXISTS `rs_data_bookings` (
  `book_id` int(10) unsigned NOT NULL auto_increment,
  `rand_code` smallint(5) unsigned default NULL,
  `book_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `object_id` smallint(5) unsigned NOT NULL default '0',
  `user_id` smallint(5) unsigned NOT NULL default '0',
  `book_start` datetime NOT NULL default '0000-00-00 00:00:00',
  `book_end` datetime NOT NULL default '0000-00-00 00:00:00',
  `validated` tinyint(1) unsigned NOT NULL default '0',
  `misc_info` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`book_id`),
  KEY `book_date` (`book_date`),
  KEY `object_id` (`object_id`),
  KEY `user_id` (`user_id`),
  KEY `book_start` (`book_start`),
  KEY `book_end` (`book_end`),
  KEY `rand_code` (`rand_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Contenu de la table `rs_data_bookings`
--


-- --------------------------------------------------------

--
-- Structure de la table `rs_data_nobookings`
--

CREATE TABLE IF NOT EXISTS `rs_data_nobookings` (
  `n` smallint(5) unsigned NOT NULL default '0',
  `book_end_id` int(10) unsigned NOT NULL default '0',
  `book_end` datetime NOT NULL default '0000-00-00 00:00:00',
  `book_start_id` int(10) unsigned NOT NULL default '0',
  `book_start` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`n`),
  KEY `book_end` (`book_end`),
  KEY `book_start` (`book_start`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `rs_data_nobookings`
--


-- --------------------------------------------------------

--
-- Structure de la table `rs_data_objects`
--

CREATE TABLE IF NOT EXISTS `rs_data_objects` (
  `object_id` smallint(5) unsigned NOT NULL auto_increment,
  `rand_code` smallint(5) unsigned default NULL,
  `object_name` varchar(50) NOT NULL default '',
  `family_id` tinyint(3) unsigned NOT NULL default '0',
  `booking_method` varchar(10) NOT NULL default 'time_based',
  `email_bookings` char(3) NOT NULL default 'yes',
  `activity_start` varchar(5) NOT NULL default '',
  `activity_end` varchar(5) NOT NULL default '',
  `activity_step` tinyint(3) unsigned NOT NULL default '0',
  `latest_update` mediumint(8) unsigned NOT NULL default '0',
  `misc_info` varchar(255) default NULL,
  PRIMARY KEY  (`object_id`),
  KEY `rand_code` (`rand_code`),
  KEY `family_id` (`family_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Contenu de la table `rs_data_objects`
--

INSERT INTO `rs_data_objects` (`object_id`, `rand_code`, `object_name`, `family_id`, `booking_method`, `email_bookings`, `activity_start`, `activity_end`, `activity_step`, `latest_update`, `misc_info`) VALUES
(1, 0, 'Test car', 1, 'time_based', 'yes', '08:00', '18:00', 60, 0, ''),
(2, 0, 'Test Room', 2, 'time_based', 'yes', '08:00', '18:00', 15, 0, ''),
(3, 0, 'Test Notebook', 3, 'time_based', 'yes', '09:00', '17:30', 15, 0, ''),
(4, 0, 'Test Projector', 4, 'time_based', 'yes', '08:00', '18:00', 60, 0, ''),
(8, 0, 'test notebook 2', 3, 'time_based', 'yes', '09:00', '18:00', 15, 0, ''),
(9, 0, 'Calculateur 1', 15, 'stacking', 'no', '00:00', '00:00', 15, 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `rs_data_permissions`
--

CREATE TABLE IF NOT EXISTS `rs_data_permissions` (
  `permission_id` smallint(5) unsigned NOT NULL auto_increment,
  `object_id` smallint(5) unsigned NOT NULL default '0',
  `user_id` smallint(5) unsigned NOT NULL default '0',
  `profile_id` tinyint(3) unsigned NOT NULL default '0',
  `permission` set('none','view','add','modify','manage') NOT NULL default 'none',
  PRIMARY KEY  (`permission_id`),
  KEY `user_id` (`user_id`),
  KEY `object_id` (`object_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='custom users rights' AUTO_INCREMENT=53 ;

--
-- Contenu de la table `rs_data_permissions`
--

INSERT INTO `rs_data_permissions` (`permission_id`, `object_id`, `user_id`, `profile_id`, `permission`) VALUES
(1, 1, 0, 1, 'none'),
(2, 1, 0, 2, 'view'),
(3, 1, 0, 3, 'modify'),
(4, 2, 0, 1, 'none'),
(5, 2, 0, 2, 'view'),
(6, 2, 0, 3, 'add'),
(7, 3, 0, 1, 'none'),
(8, 3, 0, 2, 'view'),
(9, 3, 0, 3, 'modify'),
(10, 4, 0, 1, 'view'),
(11, 4, 0, 2, 'view'),
(12, 4, 0, 3, 'modify'),
(22, 4, 0, 4, 'manage'),
(21, 3, 0, 4, 'manage'),
(20, 2, 0, 4, 'manage'),
(19, 1, 0, 4, 'manage'),
(42, 9, 0, 1, 'none'),
(41, 8, 0, 4, 'manage'),
(40, 8, 0, 3, 'modify'),
(39, 8, 0, 2, 'view'),
(45, 9, 0, 4, 'manage'),
(44, 9, 0, 3, 'modify'),
(43, 9, 0, 2, 'view'),
(38, 8, 0, 1, 'none'),
(51, 3, 4, 0, 'manage'),
(52, 3, 6, 0, 'manage');

-- --------------------------------------------------------

--
-- Structure de la table `rs_data_users`
--

CREATE TABLE IF NOT EXISTS `rs_data_users` (
  `user_id` smallint(5) unsigned NOT NULL auto_increment,
  `rand_id` smallint(5) unsigned NOT NULL default '0',
  `last_name` varchar(50) default NULL,
  `first_name` varchar(50) default NULL,
  `login` varchar(20) default NULL,
  `profile_id` tinyint(3) unsigned NOT NULL default '3',
  `email` varchar(80) default NULL,
  `password` varchar(20) default NULL,
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `language` varchar(25) NOT NULL default 'english',
  `date_format` varchar(20) NOT NULL default '',
  `user_timezone` mediumint(9) NOT NULL default '0',
  `remarks` varchar(255) default NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `login` (`login`),
  KEY `password` (`password`),
  KEY `profile_id` (`profile_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=221 ;

--
-- Contenu de la table `rs_data_users`
--

INSERT INTO `rs_data_users` (`user_id`, `rand_id`, `last_name`, `first_name`, `login`, `profile_id`, `email`, `password`, `locked`, `language`, `date_format`, `user_timezone`, `remarks`) VALUES
(4, 0, 'Admin', 'Admin', 'admin', 4, 'admin@yourdomain.com', 'admin', 0, 'french', 'd/m/Y', 0, 'Default administrator account'),
(5, 0, 'user', 'Guest', 'guest', 2, '', 'guest', 0, 'english', 'd/m/Y', 0, 'Example of guest account'),
(1, 0, 'user', 'Anonymous', 'anonymous', 1, '', 'anonymous', 0, 'english', 'd/m/Y', 0, 'For ''anyone'' read-only access. See ''Application access level'' in the settings'),
(6, 0, 'Doe', 'John', 'jdoe', 3, 'john.doe@somedomain.com', 'jdoe', 0, 'english', 'd/m/Y', 0, 'Example of user account');

-- --------------------------------------------------------

--
-- Structure de la table `rs_param`
--

CREATE TABLE IF NOT EXISTS `rs_param` (
  `param_name` varchar(25) NOT NULL default '',
  `param_value` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`param_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `rs_param`
--

INSERT INTO `rs_param` (`param_name`, `param_value`) VALUES
('users_can_customize_date', 'no'),
('validated_color', '00c000'),
('unvalidated_color', 'ff8000'),
('app_title', 'OpenBookings.org'),
('language', 'french'),
('session_timeout', '0'),
('free_color', '0000ff'),
('default_date_format', 'd/m/Y'),
('logo_file', 'diary.gif'),
('welcome_message', 'Welcome to OpenBookings.org'),
('background_color', 'eff0f8'),
('server_timezone', '0'),
('app_version', '0.7.0 CVS'),
('application_access_level', '2'),
('admin_email', 'fakeadmin@openbookings.org'),
('self_registration_mode', 'email_validation'),
('default_user_timezone', '0'),
('font_family', 'verdana'),
('font_size', '14'),
('small_font_size', '11'),
('big_font_size', '24');

-- --------------------------------------------------------

--
-- Structure de la table `rs_param_families`
--

CREATE TABLE IF NOT EXISTS `rs_param_families` (
  `family_id` tinyint(3) unsigned NOT NULL auto_increment,
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  `family_name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`family_id`),
  KEY `ordre` (`sort_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Contenu de la table `rs_param_families`
--

INSERT INTO `rs_param_families` (`family_id`, `sort_order`, `family_name`) VALUES
(1, 4, 'Cars'),
(2, 2, 'Meeting rooms'),
(3, 1, 'Notebooks'),
(4, 8, 'Video projectors'),
(15, 9, 'Calculateurs');

-- --------------------------------------------------------

--
-- Structure de la table `rs_param_lang`
--

CREATE TABLE IF NOT EXISTS `rs_param_lang` (
  `lang_id` mediumint(8) unsigned NOT NULL auto_increment,
  `english` varchar(255) default NULL,
  `french` varchar(255) default NULL,
  `german` varchar(255) default NULL,
  PRIMARY KEY  (`lang_id`),
  KEY `english` (`english`),
  KEY `french` (`french`),
  KEY `deutsch` (`german`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=841 ;

--
-- Contenu de la table `rs_param_lang`
--

INSERT INTO `rs_param_lang` (`lang_id`, `english`, `french`, `german`) VALUES
(1, 'Bookings', 'Réservations', 'Reservationen'),
(2, 'Year', 'Année', 'Jahr'),
(3, 'Family', 'Famille', 'Gruppe'),
(4, 'Object', 'Objet', 'Objekt'),
(5, 'Show year', 'Afficher l''année', 'Jahr anzeigen'),
(6, 'Availabilities', 'Disponibilités', 'Verfügbarkeit'),
(7, 'Start', 'Début', 'Start'),
(8, 'End', 'Fin', 'Ende'),
(9, 'Search', 'Rechercher', 'Suchen'),
(10, 'Add', 'Ajouter', 'Hinzufügen'),
(11, 'January', 'janvier', 'Januar'),
(12, 'February', 'février', 'Februar'),
(13, 'March', 'mars', 'März'),
(14, 'April', 'avril', 'April'),
(15, 'May', 'mai', 'Mai'),
(16, 'June', 'juin', 'Juni'),
(17, 'July', 'juillet', 'Juli'),
(18, 'August', 'août', 'August'),
(19, 'September', 'septembre', 'September'),
(20, 'October', 'octobre', 'Oktober'),
(21, 'November', 'novembre', 'November'),
(22, 'December', 'décembre', 'Dezember'),
(23, 'Monday', 'Lundi', 'Montag'),
(24, 'Tuesday', 'Mardi', 'Dienstag'),
(25, 'Wednesday', 'Mercredi', 'Mittwoch'),
(26, 'Thursday', 'Jeudi', 'Donnerstag'),
(27, 'Friday', 'Vendredi', 'Freitag'),
(28, 'Saturday', 'Samedi', 'Samstag'),
(29, 'Sunday', 'Dimanche', 'Sonntag'),
(30, 'Booker', 'Demandeur', 'Buchende Person'),
(31, 'Remarks', 'Observations', 'Bemerkungen'),
(32, 'OK', 'Enregistrer', 'Speichern'),
(33, 'Delete', 'Supprimer', 'L'),
(34, 'Object name', 'Désignation', 'Objektname'),
(35, 'Responsible', 'Responsable', 'Verantwortlich'),
(36, 'availables', 'disponibles', 'verfügbar'),
(37, 'from', 'du', 'von'),
(38, 'to', 'au', 'bis'),
(39, 'Book it !', 'Réserver', 'Reservieren !'),
(40, 'New object', 'Nouvel objet', 'Neues Objekt'),
(41, 'has valided your booking request', 'a validé votre demande de réservation', 'hat Ihre Buchungsanfrage geprüft'),
(201, 'WARNING ! Deleting this user will also delete all bookings made in his name', 'ATTENTION ! Supprimer cet utilisateur supprimera également toutes les réservations effectuées en son nom', 'ACHTUNG! Das Löschen des Benutzers wird auch alle seine Buchungen löschen'),
(43, 'Validated booking request', 'Demande de réservation validée', 'Geprüfte Buchungsanfrage'),
(44, 'Adding', 'Ajout', 'Hinzufügen'),
(46, 'Request validated', 'Demande validée', 'Anfrage überprüft'),
(47, 'Calendar', 'Calendrier', 'Kalender'),
(48, 'No more informations', 'Pas d''infos complémentaires', 'Keine weiteren Informationen'),
(49, 'has sent the following booking request', 'a posé la demande de réservation suivante', 'hat folgende Buchungsanfrage gesendet'),
(50, 'This booking has already been recorded to the calendar but needs one of the following action', 'La réservation a été inscrite au calendrier mais nécéssite de votre part l''une des actions suivantes', 'Die Buchung wurde bereits in den Kalender eingetragen, benötigt aber eine der folgenden Aktionen'),
(51, 'Booking validation request', 'Demande de validation de réservation', 'Buchungsanfrage pruefen'),
(52, 'This name is already used by another object', 'Ce nom est déjà utilisé par un autre objet', 'Dieser Name wird bereits von einem anderen Objekt benutzt'),
(53, 'You must name your object to record it', 'Vous devez nommer votre objet pour l''enregistrer', 'Sie m'),
(54, 'Availables objects', 'Objets disponibles', 'Verfügbare Objekte'),
(55, 'Actions', 'Actions', 'Aktionen'),
(56, 'User', 'Utilisateur', 'Benutzer'),
(57, 'Password', 'Mot de passe', 'Passwort'),
(58, 'disconnect', 'déconnecter', 'abmelden'),
(59, 'Settings', 'Réglages', 'Einstellungen'),
(60, 'Users', 'Utilisateurs', 'Benutzerprofile'),
(61, 'Activity start', 'Début d''activité', 'Beginn der Aktivität'),
(62, 'Activity end', 'Fin d''activité', 'Ende der Aktivität'),
(63, 'Activity step', 'Incrément', 'Stufe'),
(64, 'minutes', 'minutes', 'Minuten'),
(65, 'Validated bookings color', 'Couleur des réservations validées', 'Farbe bestätigter Buchungen'),
(66, 'Unvalidated bookings color', 'Couleur des réservations non validées', 'Farbe der Buchungsanfragen'),
(250, 'Use email to manage bookings', 'Gérer les réservations par email', NULL),
(68, 'Enabled', 'Activé', 'Aktiviert'),
(69, 'Disabled', 'Désactivé', 'Deaktiviert'),
(70, 'Language', 'Langue', 'Sprache'),
(71, 'Application settings', 'Réglages de l''application', 'Programmeinstellungen'),
(72, 'Session timeout', 'Expiration de session', 'Session Timeout'),
(73, 'seconds', 'secondes', 'Sekunden'),
(74, 'Never', 'Jamais', 'Nie'),
(76, 'Save changes', 'Enregistrer les modifications', 'Änderungen speichern'),
(77, 'Week #', 'Semaine', 'Woche'),
(78, 'User name', 'Nom de l''utilisateur', 'Benutzername'),
(79, 'Email', 'Email', 'E-mail'),
(80, 'Login', 'Connexion', 'Login'),
(160, 'Person in charge', 'Responsable', 'Verantwortlich'),
(162, 'WARNING ! Deleting an object will destroy all attached bookings', 'AVERTISSEMENT ! La suppression d''un objet entraînera celle de toutes les réservations qui lui sont attachées', 'ACHTUNG! Das Löschen eines Objekts hat die Löschung aller zugehöriger Buchungen zur Folge'),
(163, 'Do you really want to delete this object ?', 'Souhaitez-vous réellement supprimer cet objet ?', 'Wollen Sie dieses Objekt wirklich löschen ?'),
(164, 'Locked', 'Verrouillé', 'Gesperrt'),
(165, 'Admin', 'Admin', 'Admin'),
(166, 'Yes', 'Oui', 'Ja'),
(167, 'No', 'Non', 'Nein'),
(168, 'Users list', 'Liste des utilisateurs', 'Benutzerliste'),
(169, 'User details', 'Infos utilisateur', 'Benutzerinformationen'),
(170, 'Last Name', 'Nom', 'Name'),
(171, 'First Name', 'Prénom', 'Vorname'),
(172, 'Password confirm', 'Vérif mot de passe', 'Passwort bestätigen'),
(173, 'Administrator', 'Administrateur', 'Administrator'),
(174, 'Update', 'Mettre à jour', 'Aktualisieren'),
(175, 'Password was not changed as it was empty or did not meet password confirmation', 'Le mot de passe n''a pu être changé car il était vide ou différent de la confirmation', 'Das Passwort wurde nicht geändert weil es leer war oder nicht der Bestätigung entsprach'),
(176, 'Show', 'Afficher', 'Anzeigen'),
(177, 'Login/password incorrect, try again or contact the administrator', 'Login/password incorrect, essayez à nouveau ou contactez l''administrateur', 'Login/Passwort falsch, bitte nochmals versuchen oder den Administrator kontaktieren'),
(178, 'nobody in charge', 'pas de responsable', 'kein Verantwortlicher'),
(179, 'or', 'ou', 'oder'),
(180, 'Object Manager', 'Responsable d''objets', 'Objektverantwortlicher'),
(181, 'This booking was cancelled by the user even before your try to confirm it', 'Cette réservation a été annulée par l''utilisateur avant même que vous ne tentiez de la valider', 'Die Buchung wurde durch den Benutzer noch vor Ihrer Bestätigung gelöscht'),
(182, 'You are invited to check for changes on the web site', 'Vous êtes invité à vérifier sur le web qu''il n''y a pas eu de changement depuis', 'Bitte vergleichen Sie die Website, ob sich etwas ver'),
(183, 'Printed', 'Imprimé le', 'Gedruckt'),
(184, 'Family name', 'Nom de la famille', 'Gruppenname'),
(185, 'Position in list', 'Position dans la liste', 'Listenposition'),
(186, 'Before', 'Avant', 'Vor'),
(187, 'After', 'Après', 'Nach'),
(188, 'Profile', 'Profil', 'Profil'),
(189, 'Do you really want to delete this user ?', 'Souhaitez-vous réellement supprimer cet utilisateur ?', 'Wollen Sie diesen Benutzer wirklich löschen?'),
(190, 'None', 'Aucun(e)', 'Kein(e)'),
(191, 'New user', 'Nouvel utilisateur', 'Neuer Benutzer'),
(192, 'This booking cannot be recorded as is covers another one', 'Cette réservation ne peut être enregistrée car elle déborde sur une autre', 'Diese Buchung kann nicht vorgenommen werden, da sie mit einer anderen überlappt'),
(193, 'Close', 'Fermer', 'Schliessen'),
(194, 'Intro', 'Intro', 'Intro'),
(195, 'User was not added as password was empty or did not meet password confirmation', 'L''utilisateur n''a pu être ajouté car le mot de passe était vide ou différent de la confirmation', 'Der Benutzer konnte nicht erstellt werden, da das Passwort leer ist oder nicht der Bestätigung entsprach'),
(196, 'Password does not meet confirmation', 'Le mot de passe est différent de la confirmation', 'Das Passwort stimmt nicht mit der Bestätigung überein'),
(197, 'Users can add/edit objects', 'Les utilisateurs peuvent ajouter/modifier les objets', 'Die Benutzer können Objekte hinzuf'),
(198, 'Confirmation was sent to', 'Une confirmation a été envoyée à', 'Die Bestätigung wurde gesendet an'),
(199, 'has refused your booking request', 'a refusé votre demande de réservation', 'hat Ihre Buchungsanfrage abgelehnt'),
(200, 'Refused booking request', 'Demande de réservation refusée', 'Abgelehnte Buchungsanfrage'),
(202, 'The values for the fields [First name] and [Last name] are required !', 'Des valeurs sont requises pour les champs [Nom] et [Prénom] !', 'Die Felder [Name] und [Vorname] müssen angegeben werden !'),
(203, 'The booking has been confirmed but no confirmation mail was sent to the user', 'La réservation est aceptée, mais l''utilisateur n''a pas reçu l''email de confirmation', 'Die Buchung wurde akzeptiert, aber es konnte keine Bestätigungs-Mail an den Benutzer gesendet werden'),
(204, 'Cancel', 'Refuser', 'Ablehnen'),
(205, 'Accept', 'Accepter', 'Akzeptieren'),
(206, 'My Bookings', 'Mes réservations', 'Meine Reservierungen'),
(207, 'Booking date', 'Date réservation', 'Reservierungs Termin'),
(208, 'Show bookings to guests users', 'Montrer les réservations aux invités', '[Anmeldungen zeigen den Gastbenutzern]'),
(209, 'Register', 'Inscription', 'Registrieren'),
(210, 'Create your account', 'Créez votre compte', NULL),
(211, 'Update your account', 'Mise à jour de votre compte', NULL),
(212, 'New booking', 'Nouvelle réservation', NULL),
(213, 'Menu', 'Menu', NULL),
(214, 'Server timezone', 'Fuseau horaire du serveur', NULL),
(215, 'Default language', 'Langue par défaut', NULL),
(217, 'Date format', 'Format de date', NULL),
(218, 'Logo file', 'Fichier logo', NULL),
(219, 'Welcome message', 'Message de bienvenue', NULL),
(220, 'Background color', 'Couleur de fond', NULL),
(221, 'Application access level', 'Niveau d''accès à l''application', NULL),
(222, 'Anyone', 'Tout le monde', NULL),
(223, 'Guest', 'Invité', NULL),
(224, 'french', 'français', NULL),
(225, 'User timezone', 'Fuseau horaire de l''utilisateur', NULL),
(259, 'Application title', 'Titre de l''application', NULL),
(227, 'Click here for anonymous (read-only) access', 'Cliquez ici pour l''accès anonyme (lecture seule)', NULL),
(258, 'Application title', 'Titre de l''application', NULL),
(228, 'New user', 'Nouvel utilisateur', NULL),
(229, 'Delete this family ?', 'Supprimer cette famille ?', NULL),
(230, 'Booking method', 'Mode de réservation', NULL),
(231, 'Time-based', 'Selon l''heure désirée', NULL),
(232, 'Stacking', 'Par empilement', NULL),
(233, 'Generic booking permissions', 'Permissions génériques des réservations', NULL),
(234, 'View', 'Voir', NULL),
(235, 'Modify', 'Modifier', NULL),
(236, 'Everyone', 'Tout le monde', NULL),
(237, 'Guests', 'Invités', NULL),
(239, 'Custom booking permissions (overrides generic booking permissions)', 'Permissions personnalisées (supplante les permissions génériques)', NULL),
(240, 'Manage', 'Gérer', NULL),
(244, 'Set all users profiles to this language', 'Régler cette langue dans tous les profils utilisateurs', NULL),
(242, 'Example', 'Exemple', NULL),
(243, 'Use ''d'' as day, ''m'' as month, ''Y'' as year with any char as separator', 'Utilisez ''d'' pour le jour, ''m'' pour le mois, ''Y'' pour l''année avec n''importe quel séparateur', NULL),
(245, 'Default date format', 'Format de date par défaut', NULL),
(253, 'WARNING ! Deleting your profile will also delete all your bookings', 'AVERTISSEMENT ! Supprimer votre profil supprimera également toutes vos réservations', NULL),
(251, 'Timezone', 'Fuseau horaire', NULL),
(252, 'my profile', 'mon profil', NULL),
(248, 'Replace in all users profiles', 'Remplacer dans tous les profils utilisateurs', NULL),
(249, 'Users can customize date format', 'Les utilisateurs peuvent personnaliser le format de date', NULL),
(254, 'Do you really want to delete your profile ?', 'Voulez-vous vraiment supprimer votre profil ?', NULL),
(255, 'german', 'allemand', NULL),
(256, 'english', 'anglais', NULL),
(257, 'Password confirmation', 'Comfirmation du mot de passe', NULL),
(260, 'Save', 'Enregistrer', NULL),
(262, 'I have my registration code', 'J''ai mon code d''enregistrement', NULL),
(263, 'Registration code', 'Code d''enregistrement', NULL),
(264, 'Confirm registration', 'Confirmer l''enregistrement', NULL),
(265, 'Administrator email', 'Email de l''administrateur', NULL),
(266, 'Self-registration method', 'Validation des nouveaux utilisateurs', NULL),
(267, 'Automatic (no validation required)', 'Automatique (pas de validation requise)', NULL),
(268, 'New user must reply to an email', 'Le nouvel utilisateur doit répondre à un email', NULL),
(269, 'By an administrator', 'Par un administrateur', NULL),
(270, 'Registration successful !', 'Enregistrement effectué !', NULL),
(271, 'This username is already used. Please choose another one.', 'Ce login est déjà utilisé. Veuillez en choisir un autre.', NULL),
(272, 'Back to to the welcome page', 'Retour à la page de bienvenue', NULL),
(273, 'Back to the registration form', 'Retour au formulaire d''enregistrement', NULL),
(276, 'All bookings from', 'Toutes les réservations de', NULL),
(275, 'Enter validation code', 'Entrez le code de validation', NULL),
(277, 'Whole family', 'Famille complète', NULL),
(278, 'Objects', 'Objets', NULL),
(279, 'Whole family bookings', 'Réservations de la famille complète', NULL),
(280, 'No booking color', 'Couleur des zones non réservées', NULL),
(281, 'No details', 'Pas de détails', NULL),
(282, 'The booking cannot be recorded with start = end', 'La réservation ne peut être enregistrée car le début et la fin sont confondus', NULL),
(283, 'Back', 'Retour', NULL),
(285, 'Update booking', 'Mettre à jour une réservation', NULL),
(286, 'Booking update', 'Mise à jour d''une réservation', NULL),
(287, 'Do you really want to delete this booking ?', 'Voulez-vous réellement supprimer cette réservation ?', NULL),
(288, 'Duration', 'Durée', NULL),
(289, 'Activity start format is not valid (should be hh:mm)', 'Le format du début d''activité n''est pas valide (il devrait être hh:mm)', NULL),
(290, 'Activity end format is not valid (should be hh:mm)', 'Le format de la fin d''activité n''est pas valide (il devrait être hh:mm)', NULL),
(291, 'Activity step format is not valid (should be an integer between 1 and 999)', 'Le format de l''incrément n''est pas valide (ce devrait être un entier compris entre 1 et 999)', NULL),
(293, 'app root folder', 'dossier d''application', NULL),
(295, 'Tip : for all day long activity, set start and end to 00:00', 'Astuce : pour une activité continue, réglez le début et la fin sur 00:00', NULL),
(298, 'Create a new account', 'Créer un nouveau compte', NULL),
(299, 'Username', 'Nom d''utilisateur', NULL),
(300, 'Verify password', 'Vérif. mot de passe', NULL),
(301, 'Create account', 'Créer un compte', NULL),
(302, 'First access only', 'Seulement pour le premier accès', NULL),
(297, 'WARNING ! Deleting this family will move all its objects in a temporary unclassified family', 'AVERTISSEMENT ! La supression de cet famille entraînera le déplacements de tous ses objets vers la catégorie temporaire ''Non classés''', NULL),
(304, 'Default user timezone', 'Fuseau horaire utilisateur par défaut', NULL),
(306, 'No self-registration (account creation is restricted to admins)', 'Pas d''auto-enregistrement (création de compte réservée aux admins)', NULL),
(307, 'should not be empty', 'ne doit pas rester vide', NULL),
(313, 'doesn''t match with', 'ne correspond pas à', NULL),
(314, 'too short, %1 characters min', 'trop court, %1 caractères mini', NULL),
(316, 'not a valid email', 'email non valide', NULL),
(318, 'already used', 'déjà utilisé', NULL),
(341, 'Here is what to do to unlock it :', 'Voici comment le déverrouiller :', NULL),
(342, 'WARNING ! Deleting this family will move all its objects in a temporary ''Unclassified'' family', 'ATTENTION ! Supprimer cette famille déplacera tous ses objets vers la famille temporaire ''Non classé''', NULL),
(340, 'including this code', 'en incluant ce code', NULL),
(339, 'Come back to this page and fill in the login form', 'Revenez à cette page et remplissez le formulaire de connexion', NULL),
(338, 'Check your mailbox to get the registration code that was sent to you', 'Vérifiez votre boîte email pour récupérer le code d''enregistrement qui vous a été envoyé', NULL),
(336, 'Your account has been created but is locked', 'Votre compte a bien été créé mais il est verrouillé', NULL),
(344, 'Unclassified', 'Non classé', NULL),
(824, 'Booked by', 'Réservé par', NULL),
(825, 'managed by', 'géré par', NULL),
(826, 'not managed', 'non administré', NULL),
(827, 'Look for available slots', 'Chercher des crénaux disponibles', NULL),
(828, 'Available slots', 'Crénaux disponibles', NULL),
(829, 'Show available slots', 'Montrer les crénaux disponibles', NULL),
(830, 'Start date, start hour and booking duration are required to compute available slots', 'La date de début, l\\''heure de début et la durée de réservation sont requis pour calculer les crénaux disponibles', NULL),
(831, 'Please, fill in the corresponding form fields an try again', 'Remplissez les champs de formulaire correspondants et essayez à nouveau', NULL),
(832, 'No limit', 'Pas de limite', NULL),
(833, 'Choice', 'Choix', NULL),
(834, 'Week', 'Semaine', NULL),
(835, 'Localization', 'Traduction', NULL),
(836, 'Show only missing vocabulary', 'Afficher seulement le vocabulaire manquant', NULL),
(837, 'Connect', 'Connexion', 'Verbinden'),
(838, 'Users profiles', NULL, NULL),
(840, 'Utilisateurs', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `rs_param_profiles`
--

CREATE TABLE IF NOT EXISTS `rs_param_profiles` (
  `profile_id` tinyint(3) unsigned NOT NULL default '0',
  `display_order` tinyint(3) unsigned NOT NULL default '0',
  `profile_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`profile_id`),
  KEY `order` (`display_order`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `rs_param_profiles`
--

INSERT INTO `rs_param_profiles` (`profile_id`, `display_order`, `profile_name`) VALUES
(4, 4, 'Administrator'),
(3, 3, 'User'),
(2, 2, 'Guest'),
(1, 1, 'Anyone');