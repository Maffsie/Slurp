<?php
require_once('config.php');
if(isset($_COOKIE['uploadPermissions']) && $_COOKIE['uploadPermissions'] == COOKIE_DATA) {
	header('Location: /add');
	die();
}
function generate() {
	//Random number provided by rolling a die. Guaranteed to be random.
	//return 4;
	$chrs = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-+";
	$rtrn = '';
	while(strlen($rtrn) < 20) {
		$rtrn .= substr($chrs, rand(0, strlen($chrs) - 1), 1);
	}
	return $rtrn;
}
$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
if(isset($_POST['reqUname']) && strlen($_POST['reqUname']) > 0) {
	$uname = $_POST['reqUname'];
	$passwd = hash('whirlpool',$_POST['reqPasswd']);
	$whoAreYou = $_POST['doIKnowYou'];
	$suppInfo = $_POST['extraInfo'];
	$email = $_POST['email'];
	if(strlen($_POST['reqPasswd']) > 0 && strlen($_POST['reqPasswd']) <= 6)
		$err .= 'Password not long enough<br />';
	if(strlen($_POST['reqPasswd']) == 0)
		$err .= 'No password provided<br />';
	if(strlen($uname)  > 0 && strlen($uname) <= 4)
		$err .= 'Username too short<br />';
	if(strlen($uname) == 0)
		$err .= 'No username provided!<br />';
	if(strlen($whoAreYou) == 0)
		$err .= 'You didn\'t say who you are!<br />';
	if(strlen($email) == 0)
		$err .= 'You didn\'t provide an email!<br />';
	if(strlen($suppInfo) == 0)
		$info .= 'You didn\'t give us any more information on who you are. This may affect whether we give you an account or not.<br />';
	$q = $db->query("SELECT * FROM ".TB_USRS." WHERE username='$uname'");
	if($q->num_rows != 0)
		$err .= 'That username is already taken. Try another!<br />';
	if(isset($err)) {
		?>
<html>
	<head>
		<link rel='stylesheet' href='style.css' />
		<title>Request an Account</title>
	</head>
	<body>
		<?php if(isset($err)&&$err!='') { ?><div class='extra'>Error: <?php echo $err; if(isset($info)&&$info!='') echo "<br /> Extra info: $info"; ?></div><?php } ?>
		<div id='wrapper'>
			Request an Account<br />
			<form action='' method='post'>
				<span id='small'><abbr title='Must be longer than 4 characters'>Username</abbr>: </span><input type='text' name='reqUname' /><br />
				<span id='small'><abbr title='Must be longer than 6 characters'>Password</abbr>: </span><input type='password' name='reqPasswd' /><br />
				<span id='small'>Email address: </span><input type='text' name='email' /><br />
				<span id='small'>Who are you? </span><input type='text' name='doIKnowYou' /><br />
				<span id='small'>A little more info to convince us you're really you:</span><br />
				<input type='text' name='extraInfo' /><br />
				<input type='submit' value='Mmkay' />
			</form>
		</div>
	</body>
</html>
		<?php
	} else {
		if(REG_APP) {
			include('phpmailer.php');
			$mail = new PHPMailer();
			$rnd = generate();
			$q = $db->query("INSERT INTO ".TB_TMP." (tmpKey, uname, passwd, mail) VALUES ('$rnd', '$uname', '$passwd', '$email')");
			#Emails those who should be emailed.
			$toSend = "$whoAreYou is requesting an account. Their information is as follows:\n<br />Username: $uname\n<br />Relation to you: $suppInfo\n<br />\n<br />To approve this user, click <a href='http://".BASE_URL."/approve/$rnd'>here</a>.";
			$body = $toSend;
			$body = eregi_replace("[\]", '', $body);
			$mail->IsSMTP();
			$mail->SMTPAuth   = SMTP_AUTH;
			$mail->Host       = SMTP_HOST;
			$mail->Port       = SMTP_PORT;
			$mail->From       = SMTP_FROM;
			$mail->FromName   = SMTP_FROMNAME;
			$mail->Subject    = "Someone wants an account on Slurp!";
			$mail->WordWrap   = 50; // set word wrap
			$mail->MsgHTML($body);
			$mail->AddAddress(MAIL_EMAIL,MAIL_NAME);
			$mail->IsHTML(true); // send as HTML
			$mail->Send();
		} else {
			$q = $db->query("INSERT INTO ".TB_USRS." (username, password) VALUES ('$uname', '$passwd')");
		}
		?>
<html>
	<head>
		<link rel='stylesheet' href='/style.css' />
		<title>Request an Account</title>
	</head>
	<body>
		<div id='wrapper'>
			<?php if(REG_APP){ ?>Account requested. You should recieve an email if your request has been approved.<?php } else { ?>Account created, you can now log in.<?php } ?>
		</div>
	</body>
</html><?php }
} else {
	?>
<html>
	<head>
		<link rel='stylesheet' href='/style.css' />
		<title>Request an Account</title>
	</head>
	<body>
		<div id='wrapper'>
			Request an Account<br />
			<form action='' method='post'>
				<span id='small'><abbr title='Must be longer than 4 characters'>Username</abbr>: </span><input type='text' name='reqUname' /><br />
				<span id='small'><abbr title='Must be longer than 6 characters'>Password</abbr>: </span><input type='password' name='reqPasswd' /><br />
				<?php if(REG_APP) { ?><span id='small'>Email address: </span><input type='text' name='email' /><br />
				<span id='small'>Who are you? </span><input type='text' name='doIKnowYou' /><br />
				<span id='small'>A little more info to convince us you're really you:</span><br />
				<input type='text' name='extraInfo' /><br /><?php } ?>
				<input type='submit' value='Mmkay' />
			</form>
		</div>
	</body>
</html><?php } ?>