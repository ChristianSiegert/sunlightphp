<?php
class Sanitizer {
	public static function encodeHtml($string) {
		return htmlentities($string, ENT_QUOTES, mb_internal_encoding(), false);
	}

	public static function decodeHtml($string) {
		return html_entity_decode($string, ENT_QUOTES, mb_internal_encoding());
	}
}
?>