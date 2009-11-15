<?php
require_once('config.php');
$key = str_replace('/approve/','',$_SERVER['REQUEST_URI']);
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
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
if(strlen($key) < 20 || strlen($key) > 20) {
	?>
<html>
	<head>
		<link rel='stylesheet' href='/style.css' />
		<title>Invalid request</title>
	</head>
	<body>
		<div id='wrapper'>
			That request URL is invalid. Sorry!
		</div>
	</body>
</html>
<?php } else {
	$q = $db->query("SELECT * FROM ".TB_TMP." WHERE tmpKey='$key'");
	if($q->num_rows != 1) {
		?>
<html>
	<head>
		<link rel='stylesheet' href='/style.css' />
		<title>Unrecognized request</title>
	</head>
	<body>
		<div id='wrapper'>
			That request URL doesn't exist. It's possible this request has already been approved. Sorry!
		</div>
	</body>
</html>
		<?php
	} else {
		$q = $q->fetch_assoc();
		$gen = generate(64);
		$r = $db->query("INSERT INTO ".TB_USRS." (username, password, cookie_data) VALUES ('{$q['uname']}', '{$q['passwd']}', '$gen')");
		$d = $db->query("DELETE FROM ".TB_TMP." WHERE tmpKey='$key'");
		include('phpmailer.php');
		$mail = new PHPMailer();
		$body = "Your account has been approved.\n<br />Username: {$q['uname']}\n<br />Hopefully you remember what password you used.<br /><br />Note: Your email address is no longer stored in the database.";
		$body = eregi_replace("[\]", '', $body);
		$mail->IsSMTP();
		$mail->SMTPAuth   = SMTP_AUTH;
		$mail->Host       = SMTP_HOST;
		$mail->Port       = SMTP_PORT;
		$mail->From       = SMTP_FROM;
		$mail->FromName   = SMTP_FROMNAME;
		$mail->Subject    = "Slurp! account request approved!";
		$mail->WordWrap   = 50; // set word wrap
		$mail->MsgHTML($body);
		$mail->AddAddress($q['mail'],$q['uname']);
		$mail->IsHTML(true); // send as HTML
		$mail->Send();
		?>
<html>
	<head>
		<link rel='stylesheet' href='/style.css' />
		<title>Request Approved</title>
	</head>
	<body>
		<div id='wrapper'>
			Request approved! <?php echo $q['uname']; ?> has been notified.
		</div>
	</body>
</html><?php } } ?>