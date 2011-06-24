<?php
// Prevent remote access
if (!isset($_SERVER["SERVER_ADDR"])
		|| !isset($_SERVER["REMOTE_ADDR"])
		|| $_SERVER["SERVER_ADDR"] !== $_SERVER["REMOTE_ADDR"]) {
	exit;
}

$displayErrors = ini_get("display_errors");
ini_set("display_errors", true);

define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(__FILE__)));
define("APP_DIR", ROOT_DIR . DS . "app");
define("CORE_DIR", ROOT_DIR . DS . "sunlight");
define("VENDOR_DIR", APP_DIR . DS . "vendors");
define("WEBROOT_DIR", APP_DIR . DS . "webroot");
define("CCSS_DIR", WEBROOT_DIR . DS . "ccss");
define("CJS_DIR", WEBROOT_DIR . DS . "cjs");

define("BASE_URL", rtrim(dirname(dirname(dirname($_SERVER["SCRIPT_NAME"]))), "/"));
define("CSS_URL", BASE_URL . "/css");
define("JS_URL", BASE_URL . "/js");
define("CCSS_URL", BASE_URL . "/ccss");
define("CJS_URL", BASE_URL . "/cjs");

// Include the core file that contains our autoloader
require CORE_DIR . DS . "libraries" . DS . "basics.php";

// Include config file
require APP_DIR . DS . "config" . DS . "core.php";

$errors = array();

if (defined("APC_IS_ENABLED") && defined("MEMCACHE_IS_ENABLED")) {
	if (isset($_GET["cache"]) && $_GET["cache"] === "clear") {
		Libraries\Cache::clear();
		header("Location: ?cache=cleared");
		exit;
	}
} else {
	$errors[]["message"] = "Constant APC_IS_ENABLED and/or MEMCACHE_IS_ENABLED is not set.";
	$errors[]["help"] = "Set the constant in app/config/core.php.";
}

// Check PHP version
if (preg_match("/[0-9]+\.[0-9]+\.[0-9]+/", phpversion(), $phpVersion) === 1) {
	if ($phpVersion[0] < "5.3.0") {
		$errors[]["message"] = "SunlightPHP requires PHP 5.3 or higher. The installed version is $phpVersion[0].";
		$errors[]["help"] = "Upgrade PHP to version 5.3 or higher.";
	}
} else {
	$errors[]["message"] = "SunlightPHP requires PHP 5.3 or higher. The installed version could not be determined.";
	$errors[]["help"] = "Upgrade PHP to version 5.3 or higher.";
}

// Check if all necessary directories are writable
$directories = array(
	APP_DIR . DS . "tmp" . DS . "logs",
	APP_DIR . DS . "tmp" . DS . "sessions",
	WEBROOT_DIR . DS . "ccss",
	WEBROOT_DIR . DS . "cjs",
);

$unwritableDirectories = array();

foreach ($directories as $directory) {
	if (!is_writable($directory)) {
		$errors[]["message"] = "$directory is not writable.";
		$unwritableDirectories[] = $directory;
	}
}

if ($unwritableDirectories) {
	$chgrp = "sudo chgrp www-data";
	$chmod = "sudo chmod g+w";

	foreach ($unwritableDirectories as $unwritableDirectory) {
		$chgrp .= " $unwritableDirectory";
		$chmod .= " $unwritableDirectory";
	}

	$errors[]["help"] = $chgrp . "<br />" . $chmod;
}

if (!isset($_SERVER["HTTP_HOST"])) {
	$errors[]["message"] = '$_SERVER["HTTP_HOST"] does not exist.';
}

if (!isset($_SERVER["SERVER_PROTOCOL"])) {
	$errors[]["message"] = '$_SERVER["SERVER_PROTOCOL"] is not "HTTP/1.1".';
}

if (ini_get("session.auto_start") == true) {
	$errors[]["message"] = "session.auto_start is not disabled.";
}

if ($displayErrors == true) {
	$errors[]["message"] = "display_errors is not disabled.";
}

if (ini_get("error_reporting") > 0) {
	$errors[]["message"] = "error_reporting is not disabled.";
}

if (!in_array("curl", get_loaded_extensions())) {
	$errors[]["message"] = "The curl module is not installed.";
}

if (!in_array("intl", get_loaded_extensions())) {
	$errors[]["message"] = "The intl module is not installed.";
}

if (!in_array("apc", get_loaded_extensions())) {
	$errors[]["message"] = "The APC module is not installed.";
	$errors[]["help"] = "sudo apt-get install php-apc";
}

if (!in_array("memcache", get_loaded_extensions())) {
	$errors[]["message"] = "The memcache module is not installed.";
	$errors[]["help"] = "sudo apt-get install memcached php5-memcache";
}

if (!is_file("PHPUnit" . DS . "Framework.php")) {
	$errors[]["message"] = "PHPUnit is not installed.";
	$errors[]["help"] = "sudo apt-get install phpunit";
}

if (ini_get("mail.add_x_header")) {
	$errors[]["message"] = "X-header is automatically added to outgoing e-mails.";
	$errors[]["help"] = "Set \"mail.add_x_header = Off\" in /etc/php5/apache2/php.ini and /etc/php5/cli/php.ini.";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title>Maintenance panel</title>
		<style type="text/css">
			html {
				background: #fff;
				color: #444;
				font-family: "DejaVu Sans", "Lucida Grande", "Helvetica Neue", Tahoma, Helvetica, Arial, sans-serif;
				font-size: 13px;
			}

			body {
				margin: 0 auto;
				width: 760px;
			}

			a {
				text-decoration: none;
			}

			a:hover {
				text-decoration: underline;
			}

			h1 {
				font-size: 30px;
				margin: 30px 0 0;
			}

			#error-list {

			}

				.error-list-item {

				}

				.error-list-item-message {
					background-color: #F00;
				}

				.error-list-item-help {
					background-color: #0FF;
				}
		</style>
	</head>
	<body>
		<h1>Maintenance panel</h1>

		<p>
			<a href="?cache=clear">Clear APC and Memcached cache.</a>

			<?php if (isset($_GET["cache"]) && $_GET["cache"] === "cleared") { ?>
				Cache was cleared.
			<?php } ?>
		</p>

		<ul id="error-list">
			<?php foreach ($errors as $error) { ?>
				<?php if (isset($error["message"])) { ?>
					<li class="error-list-item error-list-item-message"><?php echo $error["message"]; ?></li>
				<?php } ?>

				<?php if (isset($error["help"])) { ?>
					<li class="error-list-item error-list-item-help"><?php echo $error["help"]; ?></li>
				<?php } ?>
			<?php }?>
		</ul>
	</body>
</html>