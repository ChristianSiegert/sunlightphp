<?php
class String {
	public static function camelCaseToLowerCaseUnderscore($string) {
		return ltrim(strtolower(preg_replace('#([A-Z])#', "_$1", $string)), "_");
	}

	public static function camelCaseToLowerCaseDash($string) {
		return ltrim(strtolower(preg_replace('#([A-Z])#', "-$1", $string)), "-");
	}
}
?>