<?php
require_once('config.php');
if(!isset($_COOKIE['uploadPermissions']) || substr($_COOKIE['uploadPermissions'],0,45) != COOKIE_DATA) {
	header('Location: /login');
	die();
}
//Init DB
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
$uCData = str_replace(COOKIE_DATA, '', $_COOKIE['uploadPermissions']);
$qry = $db->query("SELECT * FROM users WHERE cookie_data = '$uCData'");
if(strlen($uCData) == 0 || $qry->num_rows == 0) {
	header('Location: /login');
	die();
}
?>
<html>
	<head>
		<title>Delete - Slurp</title>
		<link rel='stylesheet' href='/style.css' />
	</head>
	<body>
		<div id='wrapper'>
<?php
$key = str_replace('/delete/','',$_SERVER['REQUEST_URI']);
$q = $db->query("SELECT * FROM main WHERE short='$key' AND isURL=2");
if($q->num_rows == 0)
	$err .= 'No such file exists.';
else { #File exists, but the user might not be allowed to delete it.
	$q = $db->query("SELECT * FROM main WHERE short='$key' AND isURL=2 AND uCookie='$uCData'");
	if($q->num_rows == 0)
		$err .= "You did not upload this file!";
}
?>
			<h1><?php echo $err; ?></h1>
<?php
if(!isset($err)) {#File exists and user's authorised to delete it
	$d = $db->query("DELETE FROM main WHERE short='$key' AND isURL=2 AND uCookie='$uCData'"); #This could also be used to delete ShortURLs, but I won't write that capability to prevent entries from being accidentally deleted.
	$q = $q->fetch_assoc();
	unlink($q['notshort']);
	?>
			<h1>File successfully deleted.</h1>
	<?php
}
?>
		</div>
	</body>
</html>