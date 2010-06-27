<?php
// Redirect to specified page if user pressed "Cancel" button in a form
if (isset($_POST["cancel"])) {
	header("Location: " . $_POST["cancel"]);
	exit;
}

function microtime_float() {
	list($microtime, $time) = explode(" ", microtime());
	return (float) $time + (float) $microtime;
}

$startTime = microtime_float();

// Commonly used directory paths and URLs
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("APP_DIR", dirname(dirname(__FILE__)));
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

// Include some core files
include(CORE_DIR . DS . "basics.php");
include(CORE_DIR . DS . "cache.php");
include(CORE_DIR . DS . "config.php");
include(CORE_DIR . DS . "dispatcher.php");
include(CORE_DIR . DS . "element.php");

// Include config file
include(APP_DIR . DS . "config" . DS . "core.php");

// Start a beautiful day of work
$dispatcher = new Dispatcher();
$dispatcher->parseParams();
$dispatcher->dispatch();

// Append statistics when debugging
if (Config::read("debug") > 0) {
	$memoryUsage = ceil(memory_get_usage() / 1024) . " KiB";
	$memoryPeakUsage = ceil(memory_get_peak_usage() / 1024) . " KiB";
	$executionTime = round((microtime_float() - $startTime) * 1000, 1);
	printf('<pre style="color: #444; margin: 2em 0 0;">Memory: %s (Peak: %s)<br />%sms<br />', $memoryUsage, $memoryPeakUsage, $executionTime);
	printf('Cache hits:   %s<br />Cache misses: %s</pre>', Cache::$fetchCount - Cache::$storeCount, Cache::$storeCount);
}
?>