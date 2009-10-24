<?php
$stage = $_POST['stageNum'];
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
		$dbT = new mysqli($tHost,$tUsr,$tPass,$tName)
		if(mysqli_connect_errno()) {
			$err = 'Could not connect to database. Error: '.mysqli_connect_error();
		} else {
			function generate() {
				//Random number provided by rolling a die. Guaranteed to be random.
				//return 4;
				$chrs = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-+";
				$rtrn = '';
				while(strlen($rtrn) < 45) {
					$rtrn .= substr($chrs, rand(0, strlen($chrs) - 1), 1);
				}
				return $rtrn;
			}
			$conf = file_get_contents('config.php');
			$fh = fopen('config.php','w+');
			str_replace('Database_Username',$tUsr,$conf);
			str_replace('Database_Password',$tPass,$conf);
			str_replace('Database_Host',$tHost,$conf);
			str_replace('Database_Name',$tName,$conf);
			$prefix = $_POST['tbPrefix'];
			str_replace('Table_Main_Name',$prefix.'main',$conf);
			str_replace('Table_Users_Name',$prefix.'users',$conf);
			str_replace('Table_Temp_Name',$prefix.'tmp',$conf);
			str_replace('Cookie_Data',generate(),$conf);
			fwrite($fh,$conf);
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
							<span id='small'><abbr title="This should be something like: smallurl.com or short.website.com">What's the base address of your domain?</abbr> </span><input type='text' name='baseDomain' value='<?php $host = $_SERVER['SERVER_NAME']; if(substr($host,0,4) == 'www.') { $host = substr($host,4) } echo $host; ?>' /> (This was auto-detected. Correct as necessary)<br />
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
		echo "oo";
		break;
	case 'userSetup':
		echo "user setup";
		break;
	case 'complete':
		echo "Setup complete";
		unlink('install.php');
		break;
}