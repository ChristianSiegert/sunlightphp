<?php
class Sanitizer {
	public static function encodeHtml($string, $doubleEncode = false) {
		return htmlentities($string, ENT_QUOTES, mb_internal_encoding(), $doubleEncode);
	}

	public static function decodeHtml($string) {
		return html_entity_decode($string, ENT_QUOTES, mb_internal_encoding());
	}
}
?>