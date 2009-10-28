<?php
require_once('config.php');
if(!isset($_COOKIE['uploadPermissions']) || $_COOKIE['uploadPermissions'] != COOKIE_DATA) {
	header('Location: /login');
	die();
}
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
$q = $db->query("SELECT * FROM ".TB_MAIN." WHERE isURL=2");
if($q->num_rows == 0) {
	$err .= "No files have been uploaded.<br />";
} else {
	$f = $q->fetch_assoc();
	while($file = $f) {
		$sz = filesize($f['notshort']);
		$out = "{$file['filename']} | {$sz}kb | {$file['short']}<br />";
	}
	$out = count($f)." files uploaded.<br /><span id='small'>$out</small>";
}
?>
<html>
	<head>
		<title>File listing - Slurp!</title>
		<link rel='stylesheet' href='/style.css' />
	</head>
	<body>
		<div id='wrapper'>
			<?php if(isset($err)) { echo $err; } else { echo $out; } ?>
		</div>
	</body>
</html>