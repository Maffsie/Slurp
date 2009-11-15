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
$fL = $db->query("SELECT * FROM main WHERE isURL = 2");
function niceSize($int) {
	if($int < 1024) {
		if($int == 1)
			$pl = '';
		else
			$pl = 's';
		return "$int byte$pl";
	}
	$int = round($int / 1024, 2);
	if($int < 1024)
		return "$int kb";
	$int = round($int / 1024, 2);
	if($int < 1024)
		return "$int mb";
	$int = round($int / 1024, 2);
	return "$int gb";
}
while($instance = $fL->fetch_assoc()) {
	$fsize = niceSize(filesize($instance['notshort']));
	$uDet = $db->query("SELECT * FROM users WHERE cookie_data = '{$instance['uCookie']}'");
	if($uDet->num_rows == 0)
		$username = "unknown";
	else {
		$uDet = $uDet->fetch_assoc();
		$username = $uDet['username'];
	}
	$out .= "<a href=\"/{$instance['short']}\" id='small'>{$instance['filename']}</a> ($fsize) - Uploaded by $username";
	if($instance['uCookie'] == $uCData)
		$out .= " - <a href=\"/delete/{$instance['short']}\" id='small'>Delete?</a>";
	$out .= "<br />\n\t\t\t";
}
?>
<html>
	<head>
		<title>List of files uploaded - Slurp</title>
		<link rel="stylesheet" href="/style.css" />
	</head>
	<body>
		<h1>List of all files uploaded</h1>
		<span id='small'>
			<?php echo $out; ?></span>
	</body>
</html>