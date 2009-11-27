<?php
$stage = $_POST['stageNum'];
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
switch($stage) {
	default:
		?>
		<html>
			<head>
				<link rel='stylesheet' href='/style.css' />
				<title>Install Slurp!</title>
			</head>
			<body>
				<div id='wrapper'>
					<h1>Welcome to the Slurp! installer.</h1>
					This install process is made to be as simple as possible, so we'll start by asking for the basics.<br />
					<form action='' method='post'>
						<span id='small'><abbr title="Chances are, you won't need to change the default value.">Database host</abbr>: </span><input type='text' name='dbHost' value='localhost' /><br />
						<span id='small'>Database username: </span><input type='text' name='dbUser' /><br />
						<span id='small'>Database password: </span><input type='password' name='dbPass' /><br />
						<span id='small'>Database name: </span><input type='text' name='dbName' /><br />
						<span id='small'>(Optional) Table prefix: </span><input type='text' name='tbPrefix' /><br />
						<input type='hidden' name='stageNum' value='dbSetup' />
						<input type='submit' value='Go!' />
					</form>
				</div>
			</body>
		</html>
		<?php
		break;
	case 'dbSetup':
		$tHost = $_POST['dbHost'];
		$tUsr = $_POST['dbUser'];
		$tPass = $_POST['dbPass'];
		$tName = $_POST['dbName'];
		$dbT = new mysqli($tHost,$tUsr,$tPass,$tName);
		if(mysqli_connect_errno()) {
			$err .= 'Could not connect to database. Error: '.mysqli_connect_error().'<br />';
		} else {
			$conf = <<<CONF
<?php
//CONFIG FILE
//Configuration variables for Slurp!
//Set your username, password, host and database name. Tables should be left the same
//Unless you know they're set different in your database.

define('DB_USR','Database_Username'); #Database Username
define('DB_PASS','Database_Password'); #Database Password
define('DB_HOST','Database_Host'); #Database Host, it's unlikely you need to change this
define('DB_NAME','Database_Name'); #Database Name
define('TB_MAIN','Table_Main_Name'); #Name for main Slurp table
define('TB_USRS','Table_Users_Name'); #Username/Passwords for users
define('TB_TMP','Table_Temp_Name'); #Pending requests for users
define('URL_LEN','URL_Length'); #Length of URLs to produce
define('COOKIE_DATA','Cookie_Data'); #Data to set in the cookie when logging in.
define('BASE_URL','Base_Site_URL'); #Base URL of your site. This should, ideally, be YourDomain.com. Don't put any slashes in, and only put a www. in if it is necessary.


//Mailer and registration settings
define('REG_APP',false); #Enable or disable manual registration approving
define('SMTP_HOST','SMTP_Host'); #SMTP Host
define('SMTP_PORT',25); #SMTP Port, leave this as it is unless you know it should be different.
define('SMTP_FROM','SMTP_From_Address'); #Email address to send from
define('SMTP_FROMNAME','SMTP_From_Friendly_Name'); #'Friendly' name for this email address
define('SMTP_AUTH',false); #Whether to use authentication or not
define('MAIL_NAME','SMTP_Owner_Name'); #Your name
define('MAIL_EMAIL','SMTP_Owner_Email'); #Your email
?>
CONF;
			$fh = fopen('slurp/config.php','w');
			$conf = str_replace('Database_Username',$tUsr,$conf);
			$conf = str_replace('Database_Password',$tPass,$conf);
			$conf = str_replace('Database_Host',$tHost,$conf);
			$conf = str_replace('Database_Name',$tName,$conf);
			$prefix = $_POST['tbPrefix'];
			$conf = str_replace('Table_Main_Name',$prefix.'main',$conf);
			$conf = str_replace('Table_Users_Name',$prefix.'users',$conf);
			$conf = str_replace('Table_Temp_Name',$prefix.'tmp',$conf);
			$conf = str_replace('Cookie_Data',generate(45),$conf);
			fwrite($fh,$conf);
			fclose($fh);
			$q1 = $dbT->query("CREATE TABLE `".$prefix."main` (`short` text NOT NULL,`notshort` text NOT NULL,`isURL` int(1) NOT NULL,`filename` text, `uCookie` varchar(64) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
			$q2 = $dbT->query("CREATE TABLE `".$prefix."tmp` (`tmpKey` varchar(20) NOT NULL,`uname` text NOT NULL,`passwd` varchar(128) NOT NULL,`mail` text NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
			$q3 = $dbT->query("CREATE TABLE `".$prefix."users` (`username` text NOT NULL,`password` varchar(128) NOT NULL, `cookie_data` varchar(64) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
			if(!$q1 || !$q2 || !$q3) {
				$err .= 'Could not create tables. Error: '.mysqli_error().'<br />Config file reset. Please try again :c<br />';
				unlink('slurp/config.php');
			}
		}
		if(!isset($err)) {
			?>
			<html>
				<head>
					<link rel='stylesheet' href='/style.css' />
					<title>Install Slurp!</title>
				</head>
				<body>
					<div id='wrapper'>
						<h1>Database setup complete!.</h1>
						This is the second-last step!<br />
						<form action='' method='post'>
							<span id='small'>How long do you want your short URLs to be? </span><input type='text' name='urlLen' value='4' /><br />
							<span id='small'><abbr title="This should be something like: smallurl.com or short.website.com">What's the base address of your domain?</abbr><br />Auto-Detected. </span><input type='text' name='baseDomain' value='<?php $host = $_SERVER['SERVER_NAME']; if(substr($host,0,4) == 'www.') { $host = substr($host,4); } echo $host; ?>' /><br />
							<input type='hidden' name='stageNum' value='extraSetup' />
							<input type='submit' value='Go!' />
						</form>
					</div>
				</body>
			</html>
			<?php
		} else {
			?>
			<html>
				<head>
					<link rel='stylesheet' href='/style.css' />
					<title>Install Slurp!</title>
				</head>
				<body>
					<div id='wrapper'>
						<h1>Welcome to the Slurp! installer.</h1>
						<b>Error: </b><?php echo $err; ?><br />
						<form action='' method='post'>
							<span id='small'><abbr title="Chances are, you won't need to change the default value.">Database host</abbr>: </span><input type='text' name='dbHost' value='localhost' /><br />
							<span id='small'>Database username: </span><input type='text' name='dbUser' /><br />
							<span id='small'>Database password: </span><input type='password' name='dbPass' /><br />
							<span id='small'>Database name: </span><input type='text' name='dbName' /><br />
							<span id='small'>(Optional) Table prefix: </span><input type='text' name='tbPrefix' /><br />
							<input type='hidden' name='stageNum' value='dbSetup' />
							<input type='submit' value='Go!' />
						</form>
					</div>
				</body>
			</html>
			<?php
		}
		break;
	case 'extraSetup':
		$urlLen = (int) $_POST['urlLen'];
		$baseD = $_POST['baseDomain'];
		if(strlen($baseD) < 3)
			$err .= 'Invalid base domain';
		if(strlen($_POST['urlLen']) == 0)
			$err .= 'URL length was not provided';
		if(isset($err)) {
			?>
			<html>
				<head>
					<link rel='stylesheet' href='/style.css' />
					<title>Install Slurp!</title>
				</head>
				<body>
					<div id='wrapper'>
						<h1>Extra configuration.</h1>
						<b>Error: </b><?php echo $err; ?><br />
						<form action='' method='post'>
							<span id='small'>How long do you want your short URLs to be? </span><input type='text' name='urlLen' value='4' /><br />
							<span id='small'><abbr title="This should be something like: smallurl.com or short.website.com">What's the base address of your domain?</abbr><br />Auto-Detected. </span><input type='text' name='baseDomain' value='<?php $host = $_SERVER['SERVER_NAME']; if(substr($host,0,4) == 'www.') { $host = substr($host,4); } echo $host; ?>' /><br />
							<input type='hidden' name='stageNum' value='extraSetup' />
							<input type='submit' value='Go!' />
						</form>
					</div>
				</body>
			</html>
			<?php
		} else {
			$cfh = fopen('slurp/config.php','r');
			while(!feof($cfh))
				$conf .= fgets($cfh);
			$fh = fopen('slurp/config.php','w');
			$conf = str_replace('URL_Length',$urlLen,$conf);
			$conf = str_replace('Base_Site_URL',$baseD,$conf);
			fwrite($fh,$conf);
			fclose($fh);
			?>
			<html>
				<head>
					<link rel='stylesheet' href='/style.css' />
					<title>Install Slurp!</title>
				</head>
				<body>
					<div id='wrapper'>
						<h1>Extra configuration complete!</h1>
						Set up your user account<br />
						<form action='' method='post'>
							<span id='small'>Username: </span><input type='text' name='uName' /><br />
							<span id='small'>Password: </span><input type='password' name='uPass' /><br />
							<input type='hidden' name='stageNum' value='userSetup' />
							<input type='submit' value='Go!' />
						</form>
					</div>
				</body>
			</html>
			<?php
		}
		break;
	case 'userSetup':
		$uN = $_POST['uName'];
		$uP = $_POST['uPass'];
		if(strlen($uN) == 0)
			$err .= 'No username provided!<br />';
		if(strlen($uN) > 0 && strlen($uN) <= 4)
			$err .= 'Username too short!<br />';
		if(strlen($uP) == 0)
			$err .= 'No password provided!<br />';
		if(strlen($uP) > 0 && strlen($uP) <= 6)
			$err .= 'Password too short!<br />';
		if(!isset($err)) {
			$uP = hash('whirlpool',$uP);
			$db = new mysqli(DB_HOST,DB_USR,DB_PASS,DB_NAME);
			$uC = generate(64);
			$q = $db->query("INSERT INTO ".TB_USRS." (username, password, cookie_data) VALUES ('$uN', '$uP', '$uC')");
			if(!$q)
				$err .= 'Could not create user. MySQL said: '.mysqli_error().'<br />';
		}
		if(isset($err)) {
			?>
			<html>
				<head>
					<link rel='stylesheet' href='/style.css' />
					<title>Install Slurp!</title>
				</head>
				<body>
					<div id='wrapper'>
						<h1>Set up your user account.</h1>
						<b>Error:</b> <?php echo $err; ?><br />
						<form action='' method='post'>
							<span id='small'>Username: </span><input type='text' name='uName' /><br />
							<span id='small'>Password: </span><input type='password' name='uPass' /><br />
							<input type='hidden' name='stageNum' value='userSetup' />
							<input type='submit' value='Go!' />
						</form>
					</div>
				</body>
			</html>
			<?php		
		} else {
			?>
			<html>
				<head>
					<link rel='stylesheet' href='/style.css' />
					<title>Installation complete!</title>
				</head>
				<body>
					<div id='wrapper'>
						<h1>User account created!</h1>
						Click below to delete this installer and log in. <span id='small'>It's recommended that the installer is deleted, for security purposes.</span><br />
						<form action='' method='post'>
							<input type='hidden' name='stageNum' value='complete' />
							<input type='submit' value='Login!' />
						</form>
					</div>
				</body>
			</html>
			<?php
		}
		break;
	case 'complete':
		unlink('slurp/install.php');
		unlink('slurp/upgrade.php');
		header('Location: /login');
		break;
}
?>