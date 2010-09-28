<?php
class LogComponent {
	/**
	 * Writes error messages to the error log file.
	 *
	 * @param string $message Message added to the error log.
	 * @return boolean Returns false on failure, true on success.
	 */
	public function addError($message) {
		$backtrace = debug_backtrace();

		$path = APP_DIR . DS . "tmp" . DS . "logs";
		$filename =  $path . DS . date("Y-m-d") . ".errors";
		$logEntry = date("H:i:s") . " " . $backtrace[1]["class"] . $backtrace[1]["type"] . $backtrace[1]["function"] . "() line " . $backtrace[0]["line"] . ": $message\n";

		return is_writable($path) && ($handle = fopen($filename, "a")) && fwrite($handle, $logEntry) && fclose($handle);
	}
}
?>