<?php
session_start();
require_once('config.php');
if(!isset($_COOKIE['uploadPermissions']) || substr($_COOKIE['uploadPermissions'],0,45) != COOKIE_DATA) {
	header('Location: /login');
	die();
}
//Init DB
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
$uCData = str_replace(COOKIE_DATA, '', $_COOKIE['uploadPermissions']);
$qry = $db->query("SELECT * FROM ".TB_USRS." WHERE cookie_data = '$uCData'");
if(strlen($uCData) == 0 || $qry->num_rows == 0) {
	header('Location: /login');
	die();
}
if(!isset($_SESSION['zFiles']) || count($_SESSION['zFiles']) == 0) {
	header('Location: /files');
	die();
}
$fn = "stored/".time().".zip";
$zip = new ZipArchive;	
$zh = $zip->open($fn, ZipArchive::CREATE);
if($zh === true) {
	foreach($_SESSION['zFiles'] as $short) {
		$q = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$short' AND isURL = 2");
		if($q->num_rows > 0) {
			$q = $q->fetch_assoc();
			$zip->addFromString($q['filename'], file_get_contents($q['notshort']));
		}
	}
	$zip->close();
	header('Content-Length: '.filesize($fn));
	header("Content-Disposition: filename=$fn");
	header('Content-Type: application/octet-stream');
	readfile($fn);
	unlink($fn);
}
?>