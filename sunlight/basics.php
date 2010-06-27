<?php
/**
 * Outputs the formatted value, prefixed with the filename and line number.
 *
 * @param mixed $value
 */
function debug($value) {
	$backtrace = debug_backtrace();
	$file = substr($backtrace[0]["file"], strlen(ROOT_DIR) + 1);

	printf('<pre><span style="display: block; font-weight: bold;">%s (line %s)</span>%s</pre>', $file, $backtrace[0]["line"], print_r($value, true));
}
?>