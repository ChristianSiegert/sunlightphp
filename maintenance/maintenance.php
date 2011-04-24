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
	$errors[] = "Constant APC_IS_ENABLED and/or MEMCACHE_IS_ENABLED is not set.";
}

// Check PHP version
if (preg_match("/[0-9]+\.[0-9]+\.[0-9]+/", phpversion(), $phpVersion) === 1) {
	if ($phpVersion[0] < "5.3.0") {
		$errors[] = "SunlightPHP requires PHP 5.3 or higher. The installed version is $phpVersion[0].";
	}
} else {
	$errors[] = "SunlightPHP requires PHP 5.3 or higher. The installed version could not be determined.";
}

$logsDir = APP_DIR . DS . "tmp" . DS . "logs";
if (!is_writable($logsDir)) {
	$errors[] = "$logsDir is not writable.";
}

$sessionsDir = APP_DIR . DS . "tmp" . DS . "sessions";
if (!is_writable($sessionsDir)) {
	$errors[] = "$sessionsDir is not writable.";
}

$ccssDir = WEBROOT_DIR . DS . "ccss";
if (!is_writable($ccssDir)) {
	$errors[] = "$ccssDir is not writable.";
}

$cjsDir = WEBROOT_DIR . DS . "cjs";
if (!is_writable($cjsDir)) {
	$errors[] = "$cjsDir is not writable.";
}

if (!isset($_SERVER["HTTP_HOST"])) {
	$errors[] = '$_SERVER["HTTP_HOST"] does not exist.';
}

if (!isset($_SERVER["SERVER_PROTOCOL"])) {
	$errors[] = '$_SERVER["SERVER_PROTOCOL"] is not "HTTP/1.1".';
}

if (ini_get("session.auto_start") == true) {
	$errors[] = "session.auto_start is not disabled.";
}

if ($displayErrors == true) {
	$errors[] = "display_errors is not disabled.";
}

if (ini_get("error_reporting") > 0) {
	$errors[] = "error_reporting is not disabled.";
}

if (!in_array("curl", get_loaded_extensions())) {
	$errors[] = "The curl module is not installed.";
}

if (!in_array("intl", get_loaded_extensions())) {
	$errors[] = "The intl module is not installed.";
}

if (!in_array("apc", get_loaded_extensions())) {
	$errors[] = "The APC module is not installed.";
}

if (!in_array("memcache", get_loaded_extensions())) {
	$errors[] = "The memcache module is not installed.";
}

if (!is_file("PHPUnit" . DS . "Framework.php")) {
	$errors[] = "PHPUnit is not installed.";
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

		<ul>
			<?php foreach ($errors as $error) { ?>
				<li><?php echo $error; ?></li>
			<?php }?>
		</ul>
	</body>
</html>