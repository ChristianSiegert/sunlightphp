<?php
class Sanitizer {
	public static function encodeHtml($string, $doubleEncode = false) {
		return htmlentities($string, ENT_QUOTES, mb_internal_encoding(), $doubleEncode);
	}

	public static function decodeHtml($string) {
		return html_entity_decode($string, ENT_QUOTES, mb_internal_encoding());
	}

	public static function normalize($string) {
		// Map characters with diacritics to their base-character followed by
		// the diacritical mark, i.e.  Ú => U´ and á => a`
		$string = Normalizer::normalize($string, Normalizer::FORM_D);

		// Remove diacritics
		$string = preg_replace('/\pM/u', "", $string);

		// Map non-Latin characters to similar looking Latin ones
		$string = preg_replace('/\x{00df}/u', "s", $string); // ß => s
		$string = preg_replace('/\x{00c6}/u', "A", $string); // Æ => A
		$string = preg_replace('/\x{00e6}/u', "a", $string); // æ => a
		$string = preg_replace('/\x{0132}/u', "I", $string); // ? => I
		$string = preg_replace('/\x{0133}/u', "i", $string); // ? => i
		$string = preg_replace('/\x{0152}/u', "O", $string); // Œ => O
		$string = preg_replace('/\x{0153}/u', "o", $string); // œ => o

		$string = preg_replace('/\x{00d0}/u', "D", $string); // Ð => D
		$string = preg_replace('/\x{0110}/u', "D", $string); // Ð => D
		$string = preg_replace('/\x{00f0}/u', "d", $string); // ð => d
		$string = preg_replace('/\x{0111}/u', "d", $string); // d => d
		$string = preg_replace('/\x{0126}/u', "H", $string); // H => H
		$string = preg_replace('/\x{0127}/u', "h", $string); // h => h
		$string = preg_replace('/\x{0131}/u', "i", $string); // i => i
		$string = preg_replace('/\x{0138}/u', "k", $string); // ? => k
		$string = preg_replace('/\x{013f}/u', "L", $string); // ? => L
		$string = preg_replace('/\x{0141}/u', "L", $string); // L => L
		$string = preg_replace('/\x{0140}/u', "l", $string); // ? => l
		$string = preg_replace('/\x{0142}/u', "l", $string); // l => l
		$string = preg_replace('/\x{014a}/u', "N", $string); // ? => N
		$string = preg_replace('/\x{0149}/u', "n", $string); // ? => n
		$string = preg_replace('/\x{014b}/u', "n", $string); // ? => n
		$string = preg_replace('/\x{00d8}/u', "O", $string); // Ø => O
		$string = preg_replace('/\x{00f8}/u', "o", $string); // ø => o
		$string = preg_replace('/\x{017f}/u', "s", $string); // ? => s
		$string = preg_replace('/\x{00de}/u', "T", $string); // Þ => T
		$string = preg_replace('/\x{0166}/u', "T", $string); // T => T
		$string = preg_replace('/\x{00fe}/u', "t", $string); // þ => t
		$string = preg_replace('/\x{0167}/u', "t", $string); // t => t

		$string = mb_strtolower($string);
		return $string;
	}

	/**
	 * Encodes non-ASCII characters.
	 *
	 * @param string $url
	 */
	public static function encodeUrl($url) {
		$encodedUrl = preg_replace_callback('/([^ !"#$%&\'()*+,\-.\/0-9:;<=>?@A-Z[\\]^_`a-z{|}~])/u', function($match) {
			return rawurlencode($match[1]);
		}, $url);

		return $encodedUrl;
	}

	/**
	 * Returns an excerpt of a text. Successive whitespace characters are
	 * combined into one. Leading and trailing whitespace is removed.
	 *
	 * @param string $text
	 * @param integer $length Number of characters until the text gets cut off
	 * @param string $ellipsis String to append if text had to be cut off. Defaults to "...".
	 */
	public static function excerpt($text, $length, $ellipsis = "...") {
		$text = trim(preg_replace('#\s+#', " ", $text));
		return mb_strlen($text) > $length ? mb_strcut($text, 0, $length) . $ellipsis : $text;
	}
}
?>