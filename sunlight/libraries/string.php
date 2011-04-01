<?php
class String {
	public static function camelCaseToLowerCaseUnderscore($string) {
		return ltrim(strtolower(preg_replace('#([A-Z])#', "_$1", $className)), "_");
	}
}
?>