<?php
$url = str_replace('/','',$_SERVER['REQUEST_URI']);
require_once('config.php');
if($url == 'style.css') {
	include('style.css');
	die();
}
if(file_exists('slurp/install.php') && $url == 'install') {
	include('install.php');
	die();
}
if(DB_HOST == 'Database_Host' && DB_USR == 'Database_Username' && DB_PASS == 'Database_Password' && DB_NAME == 'Database_Name') {
	header('Location: /install');
	die();
}
if($url == 'add') {
	if($_POST['uploadInstead'] == 1 || $_POST['hType'] == 'file')
		include('add2.php');
	else
		include('add.php');
	die();
}
if($url == 'upload') {
	include('add2.php');
	die();
}
if($url == 'login') {
	include('login.php');
	die();
}
if($url == 'request') {
	include('request.php');
	die();
}
if(substr($url,0,7) == 'approve') {
	include('approve.php');
	die();
}
//Init DB
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
$q = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$url'");
if($q->num_rows == 0) {
} else {
	$q = $q->fetch_assoc();
	$go = $q['notshort'];
	if($q['isURL'] == 1) {
		unset($db, $q, $url);
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $go");
		unset($go);
	} else {
		unset($db, $url);
		header('Content-Length: '.filesize($go));
		$mime = getimagesize($go);
		header("Content-Disposition: filename={$q['filename']}");
		unset($s);
		if($mime) {
			header("Content-Type: {$mime['mime']}");
		} else {
			header("Content-Type: application/octet-stream");
		}
		readfile($go);
		unset($go);
		die();
	}
}
?>