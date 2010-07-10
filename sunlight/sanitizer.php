<?php
class Sanitizer {
	public static function html($string) {
		return htmlentities($string, ENT_QUOTES, "UTF-8", false);
	}
}
?>