<html>
	<head>
		<title>Database Upgrade - Slurp</title>
		<link rel='stylesheet' href='/style.css'>
	</head>
	<body>
		<div id='wrapper'>
<?php
function generate($len) {
	//Random number provided by rolling a die. Guaranteed to be random.
	//return 4;
	$chrs = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-+";
	$rtrn = '';
	while(strlen($rtrn) < $len) {
		$rtrn .= substr($chrs, rand(0, strlen($chrs) - 1), 1);
	}
	return $rtrn;
}
if(!file_exists('slurp/config.php'))
	$err .= "Slurp! isn't installed!<br />";

if(!isset($err)) {
	require_once('config.php');
	#Begin DB upgrade
	$cCount = 0;
	echo "\t\t\t<span id='small'><div align='left'><ul>\n\t\t\t\t";
	echo "<li>Connecting to database...</li>\n\t\t\t\t";
	$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
	echo "<li>DB connection successful.</li>\n\t\t\t\t";
	echo "<li>Checking users table...</li>\n\t\t\t\t";
	$uT = $db->query("SHOW COLUMNS FROM `".TB_USRS."`");
	$uT = $uT->fetch_assoc();
	if(!array_key_exists('cookie_data',$uT)) {
		echo "<li>Upgrading Users table...</li>\n\t\t\t\t";
		$q = $db->query("ALTER TABLE ".TB_USRS." ADD cookie_data varchar(64) NOT NULL");
		$v = $db->query("SELECT * FROM ".TB_USRS." WHERE cookie_data = ''");
		while($usr = $v->fetch_assoc()) {
			$gen = generate(64);
			$f = $db->query("MODIFY ".TB_USRS." SET cookie_data='$gen' WHERE username='{$usr['username']}'");
			$cCount++;
		}
		$cCount++;
		echo "<li>Users table successfully upgraded</li>\n\t\t\t\t";
	}
	echo "<li>Checking Main table...</li>\n\t\t\t\t";
	$mT = $db->query("SHOW COLUMNS FROM `".TB_MAIN."`");
	$mT = $mT->fetch_assoc();
	if(!array_key_exists('uCookie',$mT)) {
		echo "<li>Upgrading Main table...</li>\n\t\t\t\t";
		$q = $db->query("ALTER TABLE ".TB_MAIN." ADD uCookie varchar(64) NULL");
		$cCount++;
		echo "<li>Main table successfully upgraded</li>\n\t\t\t\t";
	}
	echo "<li>Checking if installer exists...</li>\n\t\t\t\t";
	if(file_exists('slurp/install.php')) {
		echo "<li>Installer found. Deleting installer...</li>\n\t\t\t\t";
		unlink('slurp/install.php');
		"<li>Installer deleted.</li>\n\t\t\t\t";
	} else
		echo "<li>Installer doesn't exist. Continuing...</li>\n\t\t\t\t";
	echo "<li><b>Upgrade completed successfully - Changes made to database: $cCount</b></li>\n\t\t\t";
	echo "</div></ul></span>\n";
}
?>
		</div>
	</body>
</html>