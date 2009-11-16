<html>
	<head>
		<title>Database Upgrade - Slurp</title>
		<link rel='stylesheet' href='/style.css'>
	</head>
	<body>
<?php
if(!file_exists('slurp/config.php'))
	$err .= "Slurp! isn't installed!<br />";

if(!isset($err)) {
	require_once('config.php');
	#Begin DB upgrade
	echo "<span id='small'><ul>";
	echo "<li>Connecting to database</li>";
	$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
	echo "<li>DB connection successful.</li>";
	echo "<li>Checking users table</li>";
	$uFlds = $db->query("SHOW COLUMNS FROM ".TB_USRS);
	$uFlds = $uFlds->fetch_assoc();