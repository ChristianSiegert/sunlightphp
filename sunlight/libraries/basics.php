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
 * from CORE_DIR by putting a similar named file into APP_DIR.
 *
 * Only certain directories are checked for a matching file. See list in
 * function.
 *
 * $className is converted from camelCase to lower-case underscore notation,
 * e.g. from "CouchDbDocument" to "couch_db_document.php".
 *
 * @param string $className
 */
function __autoload($className) {
	$filename = ltrim(strtolower(preg_replace('#([A-Z])#', "_$1", $className)), "_") . ".php";

	$possiblePaths = array(
		APP_DIR . DS . "console",
		APP_DIR . DS . "controllers",
		APP_DIR . DS . "controllers" . DS . "components",
		APP_DIR . DS . "libraries",
		APP_DIR . DS . "models",
		APP_DIR . DS . "models" . DS . "couchdb",
		APP_DIR . DS . "shells",
		APP_DIR . DS . "views",
		APP_DIR . DS . "views" . DS . "helpers",
		CORE_DIR . DS . "console",
		CORE_DIR . DS . "controllers",
		CORE_DIR . DS . "controllers" . DS . "components",
		CORE_DIR . DS . "libraries",
		CORE_DIR . DS . "models",
		CORE_DIR . DS . "models" . DS . "couchdb",
		CORE_DIR . DS . "shells",
		CORE_DIR . DS . "views",
		CORE_DIR . DS . "views" . DS . "helpers",
	);

	foreach ($possiblePaths as $possiblePath) {
		$possiblePath .= DS . $filename;

		if (is_file($possiblePath)) {
			return include $possiblePath;
		}
	}
}
?>