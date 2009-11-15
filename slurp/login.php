<?php
require_once('config.php');
if(isset($_COOKIE['uploadPermissions']) && $_COOKIE['uploadPermissions'] == COOKIE_DATA) {
	header('Location: /add');
	die();
}
if($_POST['doLogin'] == 1) {
	$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
	$uname = $_POST['uname'];
	$pass = hash('whirlpool',$_POST['passwd']);
	$q = $db->query("SELECT * FROM ".TB_USRS." WHERE username='$uname' AND password='$pass'");
	if($q->num_rows == 1) {
		$q = $q->fetch_assoc();
		setcookie('uploadPermissions',COOKIE_DATA.$q['cookie_data'],time()+60*60*24*365*2);
		header('Location: /add');
		die();
	} else {
		$err = 'Username or Password incorrect. Not got an account? <a href="/request">Ask us for one</a>. If you don\'t know who runs this website, why are you here?';
		?>
<html>
	<head>
		<link rel="stylesheet" href="/style.css" />
		<title>YOU MUST BE THIS AWESOME TO UPLOAD FILES.</title>
	</head>
	<body>
	<?php if(isset($err)&&$err!='') { ?><div class='extra'>Error: <?php echo $err; ?></div><?php } ?>
		<div id='wrapper'>
			You must log in before you can upload files.<br />
			<form action='' method='post'>
				<span id='small'>Username</span> <input type='text' name='uname' onfocus="this.hadFocus = true;" id='uname' /><br />
				<span id='small'>Password</span><input type='password' name='passwd' id='passwd' /><br />
				<script type="text/javascript">
					if (document.getElementById)
						 document.getElementById('uname').focus();
				</script>
				<input type='submit' value='Login' /><br /><br />
				<input type='hidden' name='doLogin' value='1' />
			</form><br />
			<form action='/add' method='post'>
				<input type='hidden' value='0' name='uploadInstead' />
				<input type='submit' value='Shorten?' />
			</form><br />
			<span id='small'><a href="/request">No account?</a></span>
		</div>
	</body>
</html><?php
	}
} else { ?>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>YOU MUST BE THIS AWESOME TO UPLOAD FILES.</title>
	</head>
	<body>
		<div id='wrapper'>
			You must log in before you can upload files.<br />
			<form action='' method='post'>
				<span id='small'>Username</span> <input type='text' name='uname' onfocus="this.hadFocus = true;" id='uname' /><br />
				<span id='small'>Password</span><input type='password' name='passwd' id='passwd' /><br />
				<script type="text/javascript">
					if (document.getElementById)
						 document.getElementById('uname').focus();
				</script>
				<input type='submit' value='Login' /><br /><br />
				<input type='hidden' name='doLogin' value='1' />
			</form><br />
			<form action='/add' method='post'>
				<input type='hidden' value='0' name='uploadInstead' />
				<input type='submit' value='Shorten?' />
			</form><br />
			<span id='small'><a href="/request">No account?</a></span>
		</div>
	</body>
</html><?php } ?>