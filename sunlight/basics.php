<?php
/**
 * Outputs the formatted value, prefixed with the filename and line number.
 *
 * @param mixed $value
 */
function debug($value) {
	$backtrace = debug_backtrace();

	printf('<pre><span style="display: block; font-weight: bold;">%s (line %s)</span>%s</pre>',
		substr($backtrace[0]["file"], strlen(ROOT_DIR) + 1),					// Filename
		$backtrace[0]["line"],													// Line number
		htmlentities(print_r($value, true), ENT_QUOTES, mb_internal_encoding())	// Text
	);
}
?>