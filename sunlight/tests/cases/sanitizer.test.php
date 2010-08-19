<?php
include(CORE_DIR . DS . "sanitizer.php");

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
}
?>