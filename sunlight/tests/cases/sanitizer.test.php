<?php
use Libraries\Sanitizer as Sanitizer;

require CORE_DIR . DS . "libraries" . DS . "sanitizer.php";

class SanitizerDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider encodeHtmlDataProvider
	 */
	public function testEncodeHtml($input, $expected) {
		$result = Sanitizer::encodeHtml($input);
		$this->assertSame($expected, $result);
	}

	public function encodeHtmlDataProvider() {
		return array(
			array("<p>blah</p>", "&lt;p&gt;blah&lt;/p&gt;"),
			array("Tom & Jerry", "Tom &amp; Jerry"),
			array("Tom &amp; Jerry", "Tom &amp; Jerry"),
			array("Tom &amp;mp; Jerry", "Tom &amp;mp; Jerry")
		);
	}

	/**
	 * @dataProvider excerptDataProvider
	 */
	public function testExcerpt($text, $length, $ellipsis, $provideEllipsis, $expected) {
		if ($provideEllipsis) {
			$result = Sanitizer::excerpt($text, $length, $ellipsis);
		} else {
			$result = Sanitizer::excerpt($text, $length);
		}

		$this->assertSame($expected, $result);
	}

	public function excerptDataProvider() {
		return array(
			array("This is a long sentence.", 1, "", false, "T..."),
			array("This is a long sentence.", 4, "", false, "This..."),
			array("This is a long sentence.", 5, "", false, "This ..."),
			array("This is a long sentence.", 6, "", false, "This i..."),
			array("This is a long sentence.", 7, "", false, "This is..."),

			array("This is a long sentence.", 1, " [...]", true, "T [...]"),
			array("This is a long sentence.", 4, " [...]", true, "This [...]"),
			array("This is a long sentence.", 5, " [...]", true, "This  [...]"),
			array("This is a long sentence.", 6, " [...]", true, "This i [...]"),
			array("This is a long sentence.", 7, " [...]", true, "This is [...]"),

			array("  This    is   a long  sentence.   ", 1, "", false, "T..."),
			array("  This    is   a long  sentence.   ", 4, "", false, "This..."),
			array("  This    is   a long  sentence.   ", 5, "", false, "This ..."),
			array("  This    is   a long  sentence.   ", 6, "", false, "This i..."),
			array("  This    is   a long  sentence.   ", 7, "", false, "This is..."),

			array("  This    is   a long  sentence.   ", 1, " [...]", true, "T [...]"),
			array("  This    is   a long  sentence.   ", 4, " [...]", true, "This [...]"),
			array("  This    is   a long  sentence.   ", 5, " [...]", true, "This  [...]"),
			array("  This    is   a long  sentence.   ", 6, " [...]", true, "This i [...]"),
			array("  This    is   a long  sentence.   ", 7, " [...]", true, "This is [...]"),
		);
	}
}
?>