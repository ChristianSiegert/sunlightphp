<?php
namespace Libraries;

class Log {
	/**
	 * Writes a message to the specified log file.
	 *
	 * @param string $message Message to be logged.
	 * @param string $type Type of log. Default is "errors".
	 * @return boolean True on success, false on failure.
	 */
	public static function write($message, $type = "errors") {
		$backtrace = debug_backtrace();

		$path = APP_DIR . DS . "tmp" . DS . "logs";
		$filename =  $path . DS . date("Y-m-d") . ".$type";
		$logEntry = date("H:i:s") . " " . $backtrace[1]["class"] . $backtrace[1]["type"] . $backtrace[1]["function"] . "() line " . $backtrace[0]["line"] . ": $message\n";

		return is_writable($path) && ($handle = fopen($filename, "a")) && fwrite($handle, $logEntry) && fclose($handle);
	}
}
?>