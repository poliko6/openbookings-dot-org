<?php
	header('Content-type: text/css');

	require_once "config.php";
	require_once "connect_db.php";
	require_once "functions.php";
?>

a:link { color:blue; text-decoration:none; }
a:visited { color:blue; text-decoration:none; }
a:hover { color:red; text-decoration:none; }

body {
	background:#<?php echo param_extract("background_color"); ?>;
	margin: 0px;
	font-family:verdana;
	text-align: center; /* IE bug workaround */
}

div { position:absolute; margin-left:auto; margin-right:auto; }

table { border-collapse:collapse; }
td { padding:0px; }
th { text-align:center; }

img { border:0px; }
form {padding:0px; margin:0px; }

.table1 { background:#f7f7f7; font-size:12px; }
.table1 td { padding-right:1px; border: 1px solid #6f6f6f; }

.table2 { width:100%; background:#f7f7f7; }
.table2 th { padding: 3px; border: 2px groove; background:#dfdfdf; }
.table2 td { padding: 3px; border: 2px groove; }

.table3 td { padding: 3px; border: none; }

.table4 td { padding: 0px; border: none; font-size:12px; }

.table5 { font-size:12px; background:#f7f7f7 }
.table5 th { padding:0px; border:1px solid black; font-size:12px; font-weight:normal; background:#dfdfdf }
.table5 td { padding:0px; border:1px solid black; font-size:12px }

.table6 { font-size:12px; background:#f7f7f7 }
.table6 th { padding:2px; border:1px solid black; font-size:12px; background:#dfdfdf }
.table6 td { padding:2px; border:1px solid black; font-size:12px }

.list_table { width: 100%; font-size:12px; background: #f7f7f7; }
.list_table th { padding:3px; border:2px groove; background:#dfdfdf; }
.list_table td { padding:3px; border:2px groove; text-align:center; }

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
	font-size:12px;
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
	font-size:12px;
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

#notice {
	visibility:hidden;
	position: relative;
	margin-left: auto;
	margin-right: auto;
	padding:10px;
	border:2px outset;
	background:#ffffcc;

}

.colorframe {
	position: absolute;
	border:1px solid #9EA0A1;
	background-color:#f7f7f7;
	padding:20px;
}

.div_error_info {
	padding:0px;
	font-size:12px;
	color:#ff0000;
	width:250px;
	visibility:hidden;
}

.error_info td {
	font-size:12px;
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

	font-size:12px;
	text-align:center;
	padding:3px;
	padding-bottom:0px;
	margin-left:5px;
	margin-right:0px;

	cursor:pointer;
}
