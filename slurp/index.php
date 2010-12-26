<?php
$url = str_replace('/','',$_SERVER['REQUEST_URI']); #Get base request data
if($url == 'style.css') {
	include('style.css');
	die();
}
if(!file_exists('slurp/config.php') && $url != 'install') {
	header('Location: /install');
	die();
}
if(file_exists('slurp/install.php') && $url == 'install') {
	include('install.php');
	die();
} #Theoretically this could throw itself into an infinite loop and/or simply crash, if the config was set to defaults, but the install file didn't exist.
if(file_exists('slurp/upgrade.php') && $url == 'upgrade') {
	include('upgrade.php');
	die();
}
require_once('config.php'); #Initialise base system
#'Special' URI request handling
if(substr($url,0,4) == 'sApi') { #API handling.
	include('sApi.php');
	die();
}
if($url == 'add') {
	include('add.php');
	die();
}
if($url == 'upload') {
	include('add2.php');
	die();
}
if($url == 'files') {
	include('lfiles.php');
	die();
}
if(substr($url,0,6) == 'delete') {
	include('delete.php');
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
if($url == 'zip') {
	include('zipfiles.php');
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
	} elseif($q['isURL'] == 2) {
		unset($db, $url);
		header('Content-Length: '.filesize($go));
		$mime = getimagesize($go);
		if($mime) $attn = ""; else $attn = " attachment;";
		header("Content-Disposition:$attn filename={$q['filename']}");
		unset($s);
		if($mime) {
			header("Content-Type: {$mime['mime']}");
		} else {
			header("Content-Type: application/octet-stream");
		}
		readfile($go);
		unset($go);
		die();
	} elseif($q['isURL'] == 3) {
		?>
<html>
	<head>
		<title>File Deleted</title>
		<link rel='stylesheet' href='/style.css' />
	</head>
	<body>
		<div id='wrapper'>
			<h1>Sorry, but this file has been deleted by its owner.</h1>
		</div>
	</body>
</html>
		<?php
		die();
	}
}
?>