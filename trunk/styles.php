<?php

	header('Content-type: text/css');

	require_once "config.php";
	require_once "connect_db.php";
	require_once "functions.php";

	$font_size = param_extract("font_size");
	$small_font_size = param_extract("small_font_size");
	$big_font_size = param_extract("big_font_size");
?>

a:link { color:blue; text-decoration:none; }
a:visited { color:blue; text-decoration:none; }
a:hover { color:red; text-decoration:none; }

body {
	background:#<?php echo param_extract("background_color"); ?>;
	font-family:<?php echo param_extract("font_family"); ?>;
	font-size:<?php echo param_extract("font_size"); ?>px;
}

div {
	position:absolute;
}

table {
	border-collapse:collapse;
	font-size:<?php echo $font_size; ?>px;
}

td { padding:0px; }
th { text-align:center; }
img { border:0px; }

textarea {
	font-size:<?php echo $small_font_size; ?>px;
}

form {
	padding:0px;
	margin:0px;
}

.small_text {
	font-size:<?php echo $small_font_size; ?>px;
}

.big_text {
	font-size:<?php echo $big_font_size; ?>px;
}

.table1 {
	background:#f7f7f7;
	font-size:<?php echo $small_font_size; ?>px;
}

.table1 td {
	padding-right:1px;
	border:1px solid #6f6f6f;
}

.table2 {
	width:100%;
	background:#f7f7f7;
}

.table2 th {
	padding:3px;
	border:1px solid #9EA0A1;
	background:#dfdfdf;
}

.table2 td {
	padding:3px;
	border:1px solid #9EA0A1;
}

.table3 td {
	padding:3px;
	border:none;
}

.table4 td {
	padding:0px;
	border:none;
	font-size:<?php echo $small_font_size; ?>px;
}

.table5 {
	font-size:<?php echo $small_font_size; ?>px;
	background:#f7f7f7;
}

.table5 th {
	padding:0px;
	border:1px solid black;
	font-weight:normal;
	background:#dfdfdf;
}

.table5 td {
	padding:0px;
	border:1px solid black;
}

.table6 {
	font-size:<?php echo $small_font_size; ?>px;
	background:#f7f7f7
}

.table6 th {
	padding:2px;
	border:1px solid black;
	background:#dfdfdf;
}

.table6 td {
	padding:2px;
	border:1px solid black;
}

.localize_list {
	font-size:<?php echo $medium_font_size; ?>px;
	background:#f7f7f7;
}

.localize_list th {
	padding:2px;
	border:1px solid black;
	background:#dfdfdf;
}

.localize_list td {
	padding:2px;
	border:1px solid black;
}

.localize_input {
	width:400px;
	background-color:#ffffff;
	font-size:<?php echo $medium_font_size; ?>px;
}

.list_table {
	width:100%;
	font-size:<?php echo $small_font_size; ?>px;
	background: #f7f7f7;
}

.list_table th {
	padding:3px;
	border:1px solid #9EA0A1;
	background:#dfdfdf;
}

.list_table td {
	padding:3px;
	border:1px solid #9EA0A1;
	text-align:center;
}

.object_line {
	position:relative;
	font-size:1px;
	padding:0px;
	border:none;
}

.booking_line {
	padding:0px;
	border-left:1px inset;
	border-right:1px inset;
	font-size:1px;
}

#booking_infos {
	text-align:left;
	font-size:<?php echo $small_font_size; ?>px;
	border:2px groove;
	color:#000000;
	background:#ffffcc;
	padding:2px 5px 2px 5px;
	height:62px;
	width:225px;
	visibility:hidden;
}

div.ligne1 {
	padding:0px;
	border:1px solid black;
	background:#dfdfdf;
	text-align:center;
	height:20px;
	font-weight:bold;
	font-size:<?php echo $small_font_size; ?>px;
}

.global {
	position: relative;
	margin-left:auto;
	margin-right:auto;
}

#iframe_action {
	width:0px;
	height:0px;
	visibility:hidden;
}

#iframe_action_ {
	width:500px;
	height:200px;
	visibility:visible;
}

#notice {
	visibility:hidden;
	position: relative;
	margin-left: auto;
	margin-right: auto;
	padding:10px;
	border:2px outset;
	background:#ffffcc;
}

.error_message {
	position:relative;
	color:#ff0000;
	text-align:left;
}

.colorframe {
	position: relative;
	border:1px solid #9EA0A1;
	background-color:#f7f7f7;
	padding:0px;
}

.marginframe {
	position:relative;
	margin:20px
}

.div_error_info {
	padding:0px;
	font-size:<?php echo $small_font_size; ?>px;
	color:#ff0000;
	width:250px;
	visibility:hidden;
}

.error_info td {
	font-size:<?php echo $small_font_size; ?>px;
	padding:2px;
	padding-right:10px;
	background:#ff0000;
	color:#ffffff;
}

.error_info th {
	padding:0px;
	border: none;
	text-align:right;
}

.tab_set {
	position:relative;
	height:22px;
	padding:0px;
	margin:0px;
}

.tab {
	position:relative;
	float:right;

	border:1px solid #9EA0A1;
	border-bottom:none;

	width:100px;
	height:22px;

	background-color:#dfdfdf;

	font-size:<?php echo $small_font_size; ?>px;
	text-align:center;
	padding:3px;
	padding-bottom:0px;
	margin-left:5px;
	margin-right:0px;

	cursor:pointer;
}

/* used in week.php */

.hour_tag {
	top:58px
}

.line {
	top:56px;
	width:1px;
	height:3px;
	font-size:0px;
	background:black;
}

.info {
	top:30px;
	visibility:hidden;
	background:#ffffcc;
	color:#000000;
	border: 1px ridge;
	padding:0px 3px 0px 3px;
	width:200px;
	font-size:12px;
}
