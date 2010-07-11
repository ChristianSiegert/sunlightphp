<?php
ini_set("display_errors", "1");

define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(dirname(__FILE__)))));
define("CORE_DIR", ROOT_DIR . DS . "sunlight");

include(CORE_DIR . DS . "sanitizer.php");

class SanitizerDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider htmlDataProvider
	 */
	public function testHtml($expected, $string) {
		$result = Sanitizer::encodeHtml($string);
		$this->assertSame($expected, $result);
	}

	public function htmlDataProvider() {
		return array(
			array("&lt;p&gt;blah&lt;/p&gt;", "<p>blah</p>"),
			array("Tom &amp; Jerry", "Tom & Jerry"),
			array("Tom &amp; Jerry", "Tom &amp; Jerry"),
			array("Tom &amp;mp; Jerry", "Tom &amp;mp; Jerry")
		);
	}
}
?>