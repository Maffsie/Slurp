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
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
$u = $_POST['u'];
$p = hash('whirlpool',$_POST['p']);
$chk = $db->query("SELECT * FROM ".TB_USRS." WHERE username='$u' AND password='$p'");
if($chk->num_rows == 0)
	die(header('HTTP/1.1 403 Forbidden'));
$gen = generate();
$unique = false;
//Guarantees that the URL provided will be unique
while(!$unique) {
	$g = $db->query("SELECT * FROM ".TB_MAIN." WHERE short='$gen'");
	if($g->num_rows > 0 || !specialCheck($gen))
		$gen = generate();
	else
		$unique = true;
}
$filename = 'scrn_'.date('Gidmy').'.png';
$ufile = $gen;
$servFile = "stored/$ufile";
$fcont = $_POST['fupld'];
$fh = fopen("stored/$ufile",'w');
fwrite($fh,$fcont);
fclose($fh);
$q = $db->query("INSERT INTO ".TB_MAIN." (short, notshort, isURL, filename) VALUES ('$ufile', '$servFile', '2', '$filename')");
header('HTTP/1.1 200 OK');
echo $ufile;
?>