<?php
/**
 * Prints the arguments with filename and line number.
 *
 * @param mixed $value
 */
function debug() {
	$backtrace = debug_backtrace();

	foreach (func_get_args() as $argument) {
		printf('<pre><span style="display: block; font-weight: bold;">%s (line %s)</span>%s</pre>',
			substr($backtrace[0]["file"], strlen(ROOT_DIR) + 1),						// Filename
			$backtrace[0]["line"],														// Line number
			htmlentities(print_r($argument, true), ENT_QUOTES, mb_internal_encoding())	// Text
		);
	}
}
?>