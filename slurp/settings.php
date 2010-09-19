<?php
#settings.php
#settings provider for Slurp!
function setconfig($key, $value) {
	$configfile = file_get_contents('slurp/config.php');
	if(!defined($key)) #Protect against screwing with the config
		return false;
	$const = get_defined_constants(true);
	str_replace("define('$key','".$const['user'][$key]."');", "define('$key', '$value');", $configfile);
	$chandle = fopen('slurp/config.php', 'w');
	fwrite($chandle, $configfile);
	fclose($chandle);
	return true;
}
?>
