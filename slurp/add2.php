<?php
require_once('config.php');
if(!isset($_COOKIE['uploadPermissions']) || $_COOKIE['uploadPermissions'] != COOKIE_DATA) {
	header('Location: /login');
	die();
}
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
function validate($val) {
	if(preg_match('/^([a-z0-9-\+]+)\b/i',$val))
		return true;
	else
		return false;
}
function fileValidate($fname) {
	$filename = substr($fname,strlen($fname)-4,4);
	if($filename == '.php' || $filename == '.htm' || $filename == 'html' || $fname == '.htaccess' || substr($filename,1) == '.js')
		return false;
	else
		return true;
}
function specialCheck($chk) {
	$arChk = array('add','upload','stored','admin','login','logout','install','uninstall','files','approve','sapi','style.css','config','config.php','request','register','delete','pyslurp','slurp');
	if(array_key_exists(strtolower($chk),$arChk))
		return false;
	else
		return true;
}
if(isset($_POST['doWork']) && $_POST['doWork'] == 1 && (isset($_FILES['fupld']) && $_FILES['fupld']['size'] > 0)) {
	$info = "";
	//Init DB
	$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
	if(strlen($_POST['custName']) == 0) {
		$gen = generate();
		$rgen = true;
	} else {
		$gen = $_POST['custName'];
		if(!validate($gen)) {
			$info .= "The custom name you provided ($gen) was not valid. A random URL has been generated for you instead.<br />";
			$rgen = true;
			$gen = generate();
		} else {
			$rgen = false;
		}
		if(!specialCheck($gen)) {
			$info .= "The custom name you provided ($gen) is a 'special word', meaning you're not allowed to use it. A random URL has been generated instead.<br />";
			$rgen = true;
			$gen = generate();
		}
	}
	$c = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$gen'");
	if($c->num_rows > 0) {
		$info .= "The custom name you provided ($gen) is already in use. A random URL has been generated for you instead.<br />";
		$rgen = true;
		$gen = generate();
	}
	$unique = false;
	//Guarantees that the URL provided will be unique
	while(!$unique && $rgen) {
		$g = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$gen'");
		if($g->num_rows > 0)
			$gen = generate();
		else
			$unique = true;
	}
	$file = $_FILES['fupld']['tmp_name'];
	$orgname = str_replace(' ','_',basename($_FILES['fupld']['name']));
	if(fileValidate($orgname)) {
		if(!file_exists('stored'))
			mkdir('stored');
		$toGo = "stored/$gen";
		move_uploaded_file($file,$toGo);
		$q = $db->query("INSERT INTO ".TB_MAIN." (short, notshort, isURL, filename) VALUES ('$gen','$toGo', 2, '$orgname')");
		if(!$q)
			$err = "Could not upload file.";
	} else
		$err = "Filetype not allowed.";
	if(!isset($err))
		$success = true;
	else
		$success = false;
?>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title><?php if($success == true) { ?>File uploaded<?php } else { ?>File could not be uploaded<?php } ?></title>
	</head>
	<body>
		<?php if(isset($info)&&$info!='') { ?><div class='extra'>Extra information from the uploader:<br /><?php echo $info; ?></div><br /><?php echo "\n"; } ?>
		<div id='wrapper'>
			<?php if($success == true) { ?>File successfully uploaded. Your URL is:<br />
			<a href="http://<?php echo BASE_URL; ?>/<?php echo $gen; ?>">http://<?php echo BASE_URL; ?>/<?php echo $gen; ?></a><?php
			} else { ?>
			Upload failed. Error: <?php echo $err; ?>
			<?php } ?><br />
			<span id='small'>File upload limit: <?php echo ini_get('upload_max_filesize'); ?>B</span><br />
			<form action='' method='post' enctype='multipart/form-data'>
				<input type='file' name='fupld' />
				<input type='submit' value='Upload' /><br />
				<span id='small'>(optional) Custom URL:<br />
				<input type='text' name='custName' />
				<input type='hidden' name='doWork' value='1' />
				<input type='hidden' name='hType' value='file' />
			</form><br />
			<form action='' method='post'>
				<input type='hidden' value='0' name='uploadInstead' />
				<input type='submit' value='Shorten?' />
			</form>
		</div>
	</body>
</html>
<?php
} else {
if(isset($_FILES['fupld']) && strlen(basename($_FILES['fupld']['name'])) == 0)
	$err = "You didn't upload a file.";
?>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>Upld New</title>
	</head>
	<body>
	<?php if(isset($err)&&$err!='') { ?><div class='extra'>Error: <?=$err?></div><?php } ?>
		<div id='wrapper'>
			Upload a file<br />
			<span id='small'>File upload limit: <?php echo ini_get('upload_max_filesize'); ?>B</span><br />
			<form action='' method='post' enctype='multipart/form-data'>
				<input type='file' name='fupld' />
				<input type='submit' value='Upload' /><br />
				<span id='small'>(optional) Custom URL:</span><br />
				<input type='text' name='custName' />
				<input type='hidden' name='doWork' value='1' />
				<input type='hidden' name='hType' value='file' />
			</form><br />
			<form action='' method='post'>
				<input type='hidden' value='0' name='uploadInstead' />
				<input type='submit' value='Shorten?' />
			</form>
		</div>
	</body>
</html>
<?php
}
?>
