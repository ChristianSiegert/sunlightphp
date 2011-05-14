<?php
namespace Libraries;

class String {
	public static function camelCaseToLowerCaseUnderscore($string) {
		return ltrim(strtolower(preg_replace('#([A-Z])#', "_$1", $string)), "_");
	}

	public static function camelCaseToLowerCaseDash($string) {
		return ltrim(strtolower(preg_replace('#([A-Z])#', "-$1", $string)), "-");
	}

	public static function dashToCamelCase($string) {
		return lcfirst(preg_replace_callback('#-([a-zA-Z])#', function($match) {
			return strtoupper($match[1]);
		}, trim($string, "-")));
	}
}
?>