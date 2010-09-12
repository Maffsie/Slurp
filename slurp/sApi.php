<?php
require_once('config.php');
define('DBG_LOGGING', false);
if(isset($_GET['u']) && strlen($_GET['u']) > 0)
	$s_Shorten = true;
else
	$s_Shorten = false;
function generate() {
	//Random number provided by rolling a die. Guaranteed to be random.
	//return 4;
	$chrs = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-+";
	$rtrn = '';
	while(strlen($rtrn) < URL_LEN) {
		$rtrn .= substr($chrs, rand(0, strlen($chrs) - 1), 1);
	}
	return $rtrn;
}
function check($chk) {
	if(preg_match('/^(http|https|sftp|ftp):\/\/(w{3}\.)?(([\w-]+)\.([a-z]{2,10}))+([\w\.\?&_\/=\%\-\[\]\+#]+)*\b/i', $chk))
		return true;
	else
		return false;
}
function validate($val) {
	if(preg_match('/^([a-z0-9-\+]+)\b/i',$val))
		return true;
	else
		return false;
}
function specialCheck($chk) {
	$arChk = array('add','upload','stored','admin','login','logout','install','uninstall','files','approve','sapi','style.css','config','config.php','request','register','delete','pyslurp','slurp','zip');
	if(array_key_exists(strtolower($chk),$arChk))
		return false;
	else
		return true;
}
function logInfo($text) {
	if(!DBG_LOGGING)
		return;
	$text = "[".gmdate('d/m/y H:i:s')."] $text";
	$handle = fopen("./apiLog.txt", 'a');
	fwrite($handle, $text . "\n");
	fclose($handle);
}
logInfo("Client connected.");
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
logInfo("Connected to SQL database");
logInfo("Determining operations mode");
switch($s_Shorten) {
	case true:
		logInfo("URL Shortening Mode");
		logInfo("Checking for base64 mode");
		if(isset($_GET['b64']) && $_GET['b64'] == 1) {
			logInfo("base64 mode active.");
			$url = base64_decode($_GET['u']);
		} else {
			logInfo("base64 mode not active.");
			$url = $_GET['u'];
		}
		if(substr($url,0,7) != 'http://' && substr($url,0,8) != 'https://') {
			$url = 'http://'.$url;
		}
		logInfo("Client provided URL $url, checking that it's valid.");
		if(!check($url))
			die(header("HTTP/1.1 400 Bad Request"));
		logInfo("URL valid. Continuing.");
		logInfo("Checking that URL doesn't already exist in database.");
		$c = $db->query("SELECT * FROM ".TB_MAIN." WHERE notshort='$url'");
		if(!$c)
			die(header("HTTP/1.1 503 Service Unavailable"));
		if($c->num_rows > 0) {
			logInfo("URL already shortened, providing existing link.");
			$c = $c->fetch_assoc();
			$gen = $c['short'];
			$exist = true;
		} else {
			logInfo("URL does not exist in database. Shortening.");
			logInfo("Generating Short URL.");
			//Guarantees that the URL provided will be unique
			$unique = false;
			$gen = generate();
			while(!$unique) {
				$g = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$gen'");
				if($g->num_rows > 0 || !specialCheck($gen))
					$gen = generate();
				else
					$unique = true;
			}
		}
		logInfo("Have short URL: $gen");
		if(!$exist) {
			logInfo("Sending shit to database.");
			$q = $db->query("INSERT INTO ".TB_MAIN." (short, notshort, isURL) VALUES ('$gen', '$url', 1)");
			if(!$q)
				die(header("HTTP/1.1 503 Service Unavailable"));
		}
		echo "http://".BASE_URL."/$gen";
		break;
	case false:
		logInfo("Checking POST data.");
		if($_POST['u'] == "" || $_POST['p'] == "" || $_POST['fupld'] == "")
			die(header("HTTP/1.1 418 I'm A Teapot"));
		logInfo("Checking if file upload was successful.");
		$uploaded = base64_decode($_POST['fupld']);
		if(strlen($uploaded) == 0)
			die(header("HTTP/1.1 400 Bad Request"));
		logInfo("File uploaded successfully, continuing.");
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
		break;
	default:
		die(header("HTTP/1.1 418 I'm A Teapot"));
		break;
}
?>