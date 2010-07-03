<?php
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(__FILE__));
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

$errors = array();

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

if (ini_get("display_errors") == true) {
	$errors[] = "display_errors is not disabled.";
}

if (ini_get("error_reporting") > 0) {
	$errors[] = "error_reporting is not disabled.";
}

if (!class_exists("Normalizer", false)) {
	$errors[] = "Normalizer module is not installed.";
}

debug($errors);
?>