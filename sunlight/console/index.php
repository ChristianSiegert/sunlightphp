#!/usr/bin/php -q
<?php
$startTime = microtime(true);

mb_internal_encoding("UTF-8");

// Tell code whether it operates in a shell
define("IN_SHELL", true);

// Commonly used directory paths and URLs
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("APP_DIR", dirname(dirname(dirname(__FILE__))) . DS . "app");
define("CORE_DIR", ROOT_DIR . DS . "sunlight");
define("VENDOR_DIR", APP_DIR . DS . "vendors");
define("WEBROOT_DIR", APP_DIR . DS . "webroot");

// Include the core file that contains our autoloader
include CORE_DIR . DS . "libraries" . DS . "basics.php";

// Include config file
include APP_DIR . DS . "config" . DS . "core.php";

// Start a beautiful day of work
$dispatcher = new Console\ShellDispatcher();
$dispatcher->parseParams();
$dispatcher->dispatch();

// Append statistics when debugging
if (Libraries\Config::read("debug") > 0) {
	$memoryUsage = ceil(memory_get_usage() / 1024) . " KiB";
	$memoryPeakUsage = ceil(memory_get_peak_usage() / 1024) . " KiB";
	$executionTime = round((microtime(true) - $startTime) * 1000, 1);
	$requestCount = class_exists("HttpRequest", false) ? (Libraries\HttpRequest::getCount() === 1 ? "1 query" : Libraries\HttpRequest::getCount() . " queries" ) : "0 queries";
	printf("\n\nMemory: %s (Peak: %s)\n%sms (%s)\n", $memoryUsage, $memoryPeakUsage, $executionTime, $requestCount);
	printf("Cache hits:   %d\nCache misses: %d\n\n", Libraries\Cache::$writeCount <= Libraries\Cache::$readCount ? Libraries\Cache::$readCount - Libraries\Cache::$writeCount : 0, Libraries\Cache::$writeCount);

	if (Libraries\Config::read("debug") > 1) {
		debug(get_included_files());
	}
}
?>