<?php
require_once('config.php');
require_once('settings.php');
$urllen = URL_LEN;
function generate() {
	//Random number provided by rolling a die. Guaranteed to be random.
	//return 4;
	$chrs = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-+";
	$rtrn = '';
	while(strlen($rtrn) < $urllen) {
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
if(isset($_POST['doWork']) && $_POST['doWork'] == 1 && (isset($_POST['toShorten']) && strlen($_POST['toShorten']) > 1)) {
	$info = "";
	//Init DB
	$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
	if(strlen($_POST['custName']) == 0) {
		$gen = generate();
		$rgen = true;
	} else {
		$gen = $_POST['custName'];
		if(!validate($gen) && !$rgen) {
			$info .= "The custom name you provided ($gen) was not valid. A random URL has been generated for you instead.<br />";
			$rgen = true;
			$gen = generate();
		} else {
			$rgen = false;
		}
		if(!specialCheck($gen) && !$rgen) {
			$info .= "The custom name you provided ($gen) is a 'special word', meaning you're not allowed to use it. A random URL has been generated instead.<br />";
			$rgen = true;
			$gen = generate();
		}
	}
	$c = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$gen'");
	if($c->num_rows > 0 && !$rgen) {
		$info .= "The custom name you provided ($gen) is already in use. A random URL has been generated for you instead.<br />";
		$rgen = true;
		$gen = generate();
	}
	$unique = false;
	$genc = 0;
	//Guarantees that the URL provided will be unique
	while(!$unique && $rgen) {
		$genc++;
		if($genc > (65^$urllen))
			setconfig('URL_LEN', $urllen+1);
		$urllen++;
		$g = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$gen'");
		if($g->num_rows > 0 || !specialCheck($gen))
			$gen = generate();
		else
			$unique = true;
	}
	$url = $_POST['toShorten'];
	if(substr($url,0,7) != 'http://' && substr($url,0,8) != 'https://') {
		$url = 'http://'.$url;
	}
	if(!check($url)) {
		$err = "Invalid URL - $url";
	} else {
		$c = $db->query("SELECT * FROM ".TB_MAIN." WHERE notshort='$url'");
		if($c->num_rows > 0) {
			$c = $c->fetch_assoc();
			$gen = $c['short'];
			$info .= "URL has already been shortened<br />";
		} else {
			$q = $db->query("INSERT INTO ".TB_MAIN." (short, notshort, isURL) VALUES ('$gen','$url', 1)");
			if(!$q) {
				$err = "Could not shorten URL";
			}
		}
	}
	if(!isset($err))
		$success = true;
	else
		$success = false;
	echo $db->error;
?>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title><?php if($success == true) { ?>URL shortened!<?php } else { ?>URL could not be shortened<?php } ?></title>
	</head>
	<body>
		<?php if(isset($info)&&$info!='') { ?><div class='extra'>Extra information from the shortener:<br /><?=$info?></div><br /><?php echo "\n"; } ?>
		<div id='wrapper'>
			<?php if($success == true) { ?>URL successfully shortened. Your URL is:<br />
			<a hrel="shortlink" href="http://<?php echo BASE_URL; ?>/<?php echo $gen; ?>">http://<?php echo BASE_URL; ?>/<?php echo $gen; ?></a><?php
			} else { ?>
			Shortening failed. Error: <?php echo $err; ?>
			<?php } ?><br />
			<form action='' method='post'>
				<input type='text' name='toShorten' />
				<input type='submit' value='Shorten' /><br />
				<span id='small'>(optional) Custom URL:<br />
				<input type='text' name='custName' />
				<input type='hidden' name='doWork' value='1' />
				<input type='hidden' name='hType' value='url' />
			</form><br />
			<a href='/upload' id='small'>Upload a file?</a>
		</div>
	</body>
</html>
<?php
} else {
if(isset($_POST['toShorten']) && strlen($_POST['toShorten']) == 0)
	$err = "You didn't provide a URL to shorten.";
?>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>Maek New</title>
	</head>
	<body>
	<?php if(isset($err)&&$err!='') { ?><div class='extra'>Error: <?=$err?></div><?php } ?>
		<div id='wrapper'>
			Post a URL<br />
			<form action='' method='post'>
				<input type='text' name='toShorten' onfocus="this.hadFocus = true;" id='surl' />
				<script type="text/javascript">
					if (document.getElementById)
						 document.getElementById('surl').focus();
				</script>
				<input type='submit' value='Shorten' /><br />
				<span id='small'>(optional) Custom URL:</span><br />
				<input type='text' name='custName' />
				<input type='hidden' name='doWork' value='1' />
				<input type='hidden' name='hType' value='url' />
			</form><br />
			<a href='/upload' id='small'>Upload a file?</a>
		</div>
	</body>
</html>
<?php
}
?>
