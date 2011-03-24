<?php
/**
 * Prints human-readable information about expressions.
 *
 * @param mixed $expression,... Unlimited number of expressions
 */
function debug() {
	$backtrace = debug_backtrace();

	if (IN_SHELL) {
		foreach (func_get_args() as $argument) {
			printf("%s (line %s)\n%s\n",
				substr($backtrace[0]["file"], strlen(ROOT_DIR) + 1),	// Filename
				$backtrace[0]["line"],									// Line number
				express($argument)										// Text
			);
		}
	} else {
		foreach (func_get_args() as $argument) {
			printf('<pre><span style="display: block; font-weight: bold;">%s (line %s)</span>%s</pre>',
				substr($backtrace[0]["file"], strlen(ROOT_DIR) + 1),					// Filename
				$backtrace[0]["line"],													// Line number
				htmlentities(express($argument), ENT_QUOTES, mb_internal_encoding())	// Text
			);
		}
	}
}

/**
 * Returns information about an expression. The output is valid PHP code.
 *
 * @param string $expression
 * @param integer $nestingLevel
 */
function express($expression, $nestingLevel = 0) {
	$type = gettype($expression);

	switch ($type) {
		case "NULL":
			return "null";
		case "boolean":
			return $expression === true ? "true" : "false";
		case "integer":
			return $expression;
		case "double":
			return $expression;
		case "string":
			return "\"" . mb_convert_encoding($expression, mb_internal_encoding()) . "\"";
		case "array":
			$output = "array(";

			foreach ($expression as $key => $value) {
				$output .= "\n" . str_repeat("    ", $nestingLevel + 1) . express($key) . " => " . express($value, $nestingLevel + 1) . ",";
			}

			$output = preg_replace("/,$/", "\n" . str_repeat("    ", $nestingLevel), $output);

			$output .= ")";
			return $output;
		case "object":
			return "(... object ...)";
		default:
			return "($type) $expression";
	}
}

/**
 * Includes core classes as needed.
 * @param string $className
 */
function __autoload($className) {
	include CORE_DIR . DS . strtolower($className) . ".php";
}
?>