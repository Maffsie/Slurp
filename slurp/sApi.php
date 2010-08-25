<?php
require_once('config.php');
function generate() {
	//Random number provided by rolling a die. Guaranteed to be random.
	//return 4;
	$chrs = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-+";
	$rtrn = '';
	while(strlen($rtrn) < 4) {
		$rtrn .= substr($chrs, rand(0, strlen($chrs) - 1), 1);
	}
	return $rtrn;
}
function specialCheck($chk) {
	$arChk = array('add','upload','stored','admin','login','logout','install','uninstall','files','approve','sapi','style.css','config','config.php','request','register','delete','pyslurp','slurp');
	if(array_key_exists(strtolower($chk),$arChk))
		return false;
	else
		return true;
}
function logInfo($text) {
	$text = "[".gmdate('d/m/y H:i:s')."] $text";
	$handle = fopen("./apiLog.txt", 'a');
	fwrite($handle, $text . "\n");
	fclose($handle);
}
logInfo("Client connected.");
logInfo("Checking POST data.");
if($_POST['u'] == "" || $_POST['p'] == "" || $_POST['fupld'] == "")
	die(header("HTTP/1.1 418 I'm A Teapot"));
logInfo("Checking if file upload was successful.");
$uploaded = base64_decode($_POST['fupld']);
if(strlen($uploaded) == 0)
	die(header("HTTP/1.1 400 Bad Request"));
logInfo("File uploaded successfully, continuing.");
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
logInfo("Connected to SQL database");
$u = $_POST['u'];
$p = hash('whirlpool',$_POST['p']);
logInfo("Got user/pass: $u / $p");
$chk = $db->query("SELECT * FROM ".TB_USRS." WHERE username='$u' AND password='$p'");
if($chk->num_rows != 1)
	die(header('HTTP/1.1 403 Forbidden'));
logInfo("Validated user.");
logInfo("Retrieving cookie data for logged-in user");
$chk = $chk->fetch_assoc();
$uCData = $chk['cookie_data'];
$gen = generate();
$unique = false;
logInfo("Generating shortURL for file {$_FILES['fupld']['name']}.");
//Guarantees that the URL provided will be unique
while(!$unique) {
	$g = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$gen'");
	if($g->num_rows > 0 || !specialCheck($gen))
		$gen = generate();
	else
		$unique = true;
}
logInfo("Generated short url: $gen");
$filename = 'scrn_'.time().'.png';
$ufile = $gen;
$servFile = "stored/$ufile";
$fcont = $uploaded;
$fh = fopen("stored/$ufile",'w');
fwrite($fh,$fcont);
fclose($fh);
logInfo("Wrote file to disk at location stored/$ufile");
if($q = $db->query("INSERT INTO ".TB_MAIN." (short, notshort, isURL, filename, uCookie) VALUES ('$ufile', '$servFile', '2', '$filename', '$uCData')")) {
	header('HTTP/1.1 200 OK');
	echo $ufile;
} else
	header('HTTP/1.1 503 Service Unavailable');
?>