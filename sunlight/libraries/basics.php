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
			$output = get_class($expression) . " object (";

			foreach ($expression as $key => $value) {
				$output .= "\n" . str_repeat("    ", $nestingLevel + 1) . express($key) . " => " . express($value, $nestingLevel + 1) . ",";
			}

			$output = preg_replace("/,$/", "\n" . str_repeat("    ", $nestingLevel), $output);

			$output .= ")";
			return $output;
		default:
			return "($type) $expression";
	}
}

/**
 * Includes a PHP file that matches the $className. A match in APP_DIR takes
 * precedence over a match in CORE_DIR. That means you can soft-replace a file
 * from CORE_DIR by putting a similarly named file into APP_DIR.
 *
 * The location of the file is derived from its namespace, e.g. the file for
 * class "Models\CouchDb\CouchDbDocument" is expected to be "models/couch_db/couch_db_document.php".
 *
 * Please note that the filename is $className converted from camelCase to
 * lower-case underscore notation.
 *
 * We use this rigid naming scheme to make autoloading efficient. If you prefer
 * another naming scheme, soft-replace basics.php and use your custom
 * __autoload() function. You could also simply include files by hand so the
 * autoloader is not triggered.
 *
 * @param string $className
 */
function __autoload($className) {
	$pieces = explode("\\", $className);

	foreach ($pieces as &$piece) {
		$piece = ltrim(strtolower(preg_replace('#([A-Z])#', "_$1", $piece)), "_");
	}

	$partialFilename = implode(DS, $pieces) . ".php";

	$possiblePaths = array(
		APP_DIR,
		CORE_DIR,
	);

	foreach ($possiblePaths as $possiblePath) {
		$filename = $possiblePath . DS . $partialFilename;

		if (is_file($filename)) {
			return require $filename;
		}
	}
}
?>