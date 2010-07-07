<?php
// Prevent remote access
if (!isset($_SERVER["SERVER_ADDR"])
		|| !isset($_SERVER["REMOTE_ADDR"])
		|| $_SERVER["SERVER_ADDR"] !== $_SERVER["REMOTE_ADDR"]) {
	exit;
}

define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(__FILE__)));
define("APP_DIR", ROOT_DIR . DS . "app");
define("CORE_DIR", ROOT_DIR . DS . "sunlight");
define("VENDOR_DIR", APP_DIR . DS . "vendors");
define("WEBROOT_DIR", APP_DIR . DS . "webroot");
define("CCSS_DIR", WEBROOT_DIR . DS . "ccss");
define("CJS_DIR", WEBROOT_DIR . DS . "cjs");

define("BASE_URL", dirname(dirname(dirname($_SERVER["SCRIPT_NAME"]))));
define("CSS_URL", BASE_URL . "/css");
define("JS_URL", BASE_URL . "/js");
define("CCSS_URL", BASE_URL . "/ccss");
define("CJS_URL", BASE_URL . "/cjs");

include(CORE_DIR . DS . "basics.php");
include(CORE_DIR . DS . "cache.php");
include(CORE_DIR . DS . "config.php");
include(APP_DIR . DS . "config" . DS . "core.php");

$errors = array();

if (defined("APC_IS_ENABLED") && defined("MEMCACHE_IS_ENABLED")) {
	if (isset($_GET["cache"]) && $_GET["cache"] === "clear") {
		Cache::clear();
		header("Location: ?cache=cleared");
		exit;
	}
} else {
	$errors[] = "Constant APC_IS_ENABLED and/or MEMCACHE_IS_ENABLED is not set.";
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

if (!isset($_SERVER["REMOTE_ADDR"])) {
	$errors[] = '$_SERVER["REMOTE_ADDR"] is not does not exist.';
}

if (ini_get("session.auto_start") == true) {
	$errors[] = "session.auto_start is not disabled.";
}

if (ini_get("display_errors") == true) {
	$errors[] = "display_errors is not disabled.";
}

if (ini_get("error_reporting") > 0) {
	$errors[] = "error_reporting is not disabled.";
}

if (!class_exists("Normalizer", false)) {
	$errors[] = "Normalizer module is not installed.";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta http-equiv="refresh" content="120">
    <title>Maintenance panel</title>
    <style>
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