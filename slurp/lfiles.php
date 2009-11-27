<?php
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
$fL = $db->query("SELECT * FROM ".TB_MAIN." WHERE isURL = 2");
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
if(isset($_POST['doSearch']) && strlen($_POST['searchQry']) > 0) {
	$srch = true;
	$srchTrm = $_POST['searchQry'];
	$sC = 0;
} else
	$srch = false;
function search($term,$str) {
	if(strpos(strtolower($str),strtolower($term)) !== false)
		return true;
	else
		return false;
}
if($fL->num_rows > 0) {
	$totSize = 0;
	while($instance = $fL->fetch_assoc()) {
		$fsize = niceSize(filesize($instance['notshort']));
		$uDet = $db->query("SELECT * FROM ".TB_USRS." WHERE cookie_data = '{$instance['uCookie']}'");
		if($uDet->num_rows == 0)
			$username = "unknown";
		else {
			$uDet = $uDet->fetch_assoc();
			$username = $uDet['username'];
		}
		if(!$srch || ($srch && (search($srchTrm,$instance['filename']) || search($srchTrm,$username)))) {
			$totSize += filesize($instance['notshort']);
			$out .= "<a href=\"/{$instance['short']}\" id='small'>{$instance['filename']}</a> ($fsize) - Uploaded by $username";
			if($instance['uCookie'] == $uCData)
				$out .= " - <a href=\"/delete/{$instance['short']}\" id='small'>Delete?</a>";
			$out .= "<br />\n\t\t\t";
			if($srch)
				$sC++;
		}
	}
	if($sC == 0 && $srch)
		$out = "No files matched your search query ($srchTrm)<br />\n\t\t\t";
	$totSize = niceSize($totSize);
} else
	$out = "No files have been uploaded.<br />\n\t\t\t";
if(!$srch)
	$title = "List of files uploaded";
else
	$title = "Searching files uploaded for $srchTrm ($sC results)";
?>
<html>
	<head>
		<title><?php echo $title; ?> - Slurp</title>
		<link rel="stylesheet" href="/style.css" />
	</head>
	<body>
		<h1><?php echo $title; ?></h1>
		<span id='small'>
			<?php echo $out; ?></span>
		<h2>Total file size of all files: <?php echo $totSize; ?></h2>
		<h3>Search files?</h3>
		<form action='' method='post'>
			<input type='hidden' name='doSearch' value='1' />
			<input type='text' name='searchQry' />&nbsp;
			<input type='submit' value='Search' />
		</form>
	</body>
</html>