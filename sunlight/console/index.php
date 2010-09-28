#!/usr/bin/php -q
<?php
$startTime = microtime(true);

mb_internal_encoding("UTF-8");

// Commonly used directory paths and URLs
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("APP_DIR", dirname(dirname(dirname(__FILE__))) . DS . "app");
define("CORE_DIR", ROOT_DIR . DS . "sunlight");
define("VENDOR_DIR", APP_DIR . DS . "vendors");
define("WEBROOT_DIR", APP_DIR . DS . "webroot");
define("CCSS_DIR", WEBROOT_DIR . DS . "ccss");
define("CJS_DIR", WEBROOT_DIR . DS . "cjs");

// Include some core files
include(CORE_DIR . DS . "basics.php");
include(CORE_DIR . DS . "cache.php");
include(CORE_DIR . DS . "config.php");
#include(CORE_DIR . DS . "dispatcher.php");
#include(CORE_DIR . DS . "element.php");
#include(CORE_DIR . DS . "router.php");

define("BASE_URL", dirname(dirname(dirname($_SERVER["SCRIPT_NAME"]))));
define("CSS_URL", BASE_URL . "/css");
define("JS_URL", BASE_URL . "/js");
define("CCSS_URL", BASE_URL . "/ccss");
define("CJS_URL", BASE_URL . "/cjs");

// Include config file
include(APP_DIR . DS . "config" . DS . "core.php");

// Start a beautiful day of work
$dispatcher = new Dispatcher();
$dispatcher->dispatch();

// Append statistics when debugging
if (Config::read("debug") > 0) {
	$memoryUsage = ceil(memory_get_usage() / 1024) . " KiB";
	$memoryPeakUsage = ceil(memory_get_peak_usage() / 1024) . " KiB";
	$executionTime = round((microtime(true) - $startTime) * 1000, 1);
	$queryCount = class_exists("Model") ? (Model::$queryCount === 1 ? "1 query" : Model::$queryCount . " queries" ) : "0 queries";
	printf('Memory: %s (Peak: %s)\n%sms (%s)\n', $memoryUsage, $memoryPeakUsage, $executionTime, $queryCount);
	printf('Cache hits:   %d\nCache misses: %d', Cache::$writeCount <= Cache::$readCount ? Cache::$readCount - Cache::$writeCount : 0, Cache::$writeCount);
}
?>