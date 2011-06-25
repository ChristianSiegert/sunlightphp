<?php
use Libraries\Element;

require CORE_DIR . DS . "libraries" . DS . "element.php";

class ElementDataTest extends PHPUnit_Framework_TestCase {
	public function test__construct() {
		// Tag provided
		$element = new Element("foo");
		$this->assertSame("<foo></foo>", (string) $element);

		// Tag and attributed provided
		$element = new Element("foo", array("hello" => "world"));
		$this->assertSame('<foo hello="world"></foo>', (string) $element);

		// Tag, attributes and HTML provided
		$element = new Element("foo", array("hello" => "world"), "Hi");
		$this->assertSame('<foo hello="world">Hi</foo>', (string) $element);

		// Tag, attributes and HTML provided
		$element = new Element("foo", array("hello" => "world", "html" => "No Problemo"), "Hi");
		$this->assertSame('<foo hello="world" html="No Problemo">Hi</foo>', (string) $element);
	}

	public function test__clone() {
		$element = new Element("foo");
		$element->grab(new Element("bar"));

		$elementClone = clone $element;
		$elementClone->grab(new Element("bar2"));

		$this->assertSame('<foo><bar></bar></foo>', (string) $element);
		$this->assertSame('<foo><bar></bar><bar2></bar2></foo>', (string) $elementClone);
	}

	public function test__get() {
		$element = new Element("foo");
		$this->assertSame(null, $element->bar);

		$element = new Element("foo", array("bar" => "Hello"));
		$this->assertSame("Hello", $element->bar);
	}

	public function test__set() {
		$element = new Element("foo");
		$element->bar = "Hello";
		$this->assertSame('<foo bar="Hello"></foo>', (string) $element);

		$element = new Element("foo");
		$element->bar = "Hello";
		$element->bar2 = "Hi";
		$this->assertSame('<foo bar="Hello" bar2="Hi"></foo>', (string) $element);

		$element = new Element("foo");
		$element->html = "No Problemo";
		$this->assertSame('<foo html="No Problemo"></foo>', (string) $element);
	}

	public function testInject() {
		$element = new Element("foo");

		$element2 = new Element("bar");
		$element2->inject($element);

		$this->assertSame("<foo><bar></bar></foo>", (string) $element);
	}

	public function testGrab() {
		$element = new Element("foo");
		$element->grab(new Element("bar"));

		$this->assertSame("<foo><bar></bar></foo>", (string) $element);
	}

	public function getHtml() {
		$element = new Element("foo", array(), "Hello");
		$this->assertSame("Hello", $element->getHtml());
	}

	public function setHtml() {
		$element = new Element("foo");
		$element->setHtml("Hello");
		$this->assertSame("<foo>Hello</foo>", (string) $element);
	}

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
		$element3 = new Element("a", array("href" => "/about.htm"), "Link text");

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