<?php
$startTime = microtime(true);

mb_internal_encoding("UTF-8");

// Redirect to specified page if user pressed "Cancel" button in a form
if (isset($_POST["system"]["redirectUrl"])) {
	header("Location: " . $_POST["system"]["redirectUrl"]);
	exit;
} elseif (isset($_GET["system"]["redirectUrl"])) {
	header("Location: " . $_GET["system"]["redirectUrl"]);
	exit;
}

// Tell code whether it operates in a shell
define("IN_SHELL", false);

// Commonly used directory paths and URLs
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("APP_DIR", dirname(dirname(__FILE__)));
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
$basicsFile = DS . "libraries" . DS . "basics.php";
require is_file(APP_DIR . $basicsFile) ? APP_DIR . $basicsFile : CORE_DIR . $basicsFile;

// Include config file
require APP_DIR . DS . "config" . DS . "core.php";

// Start a beautiful day of work
$dispatcher = new Libraries\Dispatcher();
$dispatcher->parseParams();
Libraries\Router::$params = $dispatcher->params;
$dispatcher->dispatch();

// Append statistics when debugging
if (Libraries\Config::read("debug") > 0) {
	$memoryUsage = ceil(memory_get_usage() / 1024) . " KiB";
	$memoryPeakUsage = ceil(memory_get_peak_usage() / 1024) . " KiB";
	$executionTime = round((microtime(true) - $startTime) * 1000, 1);
	$requestCount = class_exists("Libraries\\HttpRequest", false) ? (Libraries\HttpRequest::getCount() === 1 ? "1 query" : Libraries\HttpRequest::getCount() . " queries" ) : "0 queries";
	printf('<pre style="clear: both; color: #444; margin: 2em 0 0;">Memory: %s (Peak: %s)<br />%sms (%s)<br />', $memoryUsage, $memoryPeakUsage, $executionTime, $requestCount);
	printf('Cache hits:   %d<br />Cache misses: %d</pre>', Libraries\Cache::$writeCount <= Libraries\Cache::$readCount ? Libraries\Cache::$readCount - Libraries\Cache::$writeCount : 0, Libraries\Cache::$writeCount);

	if (Libraries\Config::read("debug") > 1) {
		debug(get_included_files());
	}
}
?>