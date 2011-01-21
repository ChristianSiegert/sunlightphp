<?php
include(CORE_DIR . DS . "element.php");

class ElementDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider toStringDataProvider
	 */
	public function testToString($element, $expected) {
		$result = $element->toString();
		$this->assertSame($expected, $result);
	}

	public function toStringDataProvider() {
		// Element without attributes
		$element1 = new Element("img");

		// Element with attributes
		$element2 = new Element("img", array(
			"alt" => "Image text",
			"height" => 220,
			"width" => 432,
		));

		// Element with content
		$element3 = new Element("a", array(
			"href" => "/about.htm",
			"html" => "Link text",
		));

		// Inject <img> into <a>
		$element4 = clone $element3;
		$element2->inject($element4);

		// <a> grabs <img>
		$element5 = clone $element3;
		$element5->grab($element2);

		// Multiple levels of nesting
		$element6 = new Element("div");
		$element6->grab($element5);

		return array(
			array($element1, '<img></img>'),
			array($element2, '<img alt="Image text" height="220" width="432"></img>'),
			array($element3, '<a href="/about.htm">Link text</a>'),
			array($element5, '<a href="/about.htm">Link text<img alt="Image text" height="220" width="432"></img></a>'),
			array($element5, '<a href="/about.htm">Link text<img alt="Image text" height="220" width="432"></img></a>'),
			array($element6, '<div><a href="/about.htm">Link text<img alt="Image text" height="220" width="432"></img></a></div>'),
		);
	}
}
?>