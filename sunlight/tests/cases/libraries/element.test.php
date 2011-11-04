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
			array($element1, '<img>'),
			array($element2, '<img alt="Image text" height="220" width="432">'),
			array($element3, '<a href="/about.htm">Link text</a>'),
			array($element5, '<a href="/about.htm">Link text<img alt="Image text" height="220" width="432"></a>'),
			array($element5, '<a href="/about.htm">Link text<img alt="Image text" height="220" width="432"></a>'),
			array($element6, '<div><a href="/about.htm">Link text<img alt="Image text" height="220" width="432"></a></div>'),
		);
	}

	/**
	 * @dataProvider addClassDataProvider
	 */
	public function testAddClass($element, $expected) {
		$result = (string) $element;
		$this->assertSame($expected, $result);
	}

	public function addClassDataProvider() {
		$element1 = new Element("div");
		$element1->addClass("foo");

		$element2 = new Element("div", array("class" => "foo"));
		$element2->addClass("bar");

		return array(
			array($element1, '<div class="foo"></div>'),
			array($element2, '<div class="foo bar"></div>'),
		);
	}

	/**
	 * @dataProvider removeClassDataProvider
	 */
	public function testRemoveClass($element, $expected) {
		$result = (string) $element;
		$this->assertSame($expected, $result);
	}

	public function removeClassDataProvider() {
		// Remove class when class attribute doesn't exist
		$element1 = new Element("div");
		$element1->removeClass("foo");

		// Remove class
		$element2 = new Element("div", array("class" => "foo"));
		$element2->removeClass("foo");

		// Remove class that doesn't exist
		$element3 = new Element("div", array("class" => "foo"));
		$element3->removeClass("bar");

		// Remove second of two classes
		$element4 = new Element("div", array("class" => "foo bar"));
		$element4->removeClass("bar");

		// Remove first of two classes
		$element5 = new Element("div", array("class" => "foo bar"));
		$element5->removeClass("foo");

		// Remove first of three classes
		$element6 = new Element("div", array("class" => "foo bar hello"));
		$element6->removeClass("foo");

		// Remove second of three classes
		$element7 = new Element("div", array("class" => "foo bar hello"));
		$element7->removeClass("bar");

		// Remove third of three classes
		$element8 = new Element("div", array("class" => "foo bar hello"));
		$element8->removeClass("hello");

		return array(
			array($element1, '<div></div>'),
			array($element2, '<div></div>'),
			array($element3, '<div class="foo"></div>'),
			array($element4, '<div class="foo"></div>'),
			array($element5, '<div class="bar"></div>'),
			array($element6, '<div class="bar hello"></div>'),
			array($element7, '<div class="foo hello"></div>'),
			array($element8, '<div class="foo bar"></div>'),
		);
	}

	/**
	 * @dataProvider hasClassDataProvider
	 */
	public function testHasClass($element, $className, $expected) {
		$result = $element->hasClass($className);
		$this->assertSame($expected, $result);
	}

	public function hasClassDataProvider() {
		$element1 = new Element("div");
		$element2 = new Element("div", array("class" => ""));
		$element3 = new Element("div", array("class" => "foo"));
		$element4 = new Element("div", array("class" => "foo bar"));
		$element5 = new Element("div", array("class" => "foo bar hello"));

		return array(
			array($element1, "foo", false),
			array($element2, "foo", false),
			array($element3, "foo", true),

			array($element4, "foo", true),
			array($element4, "bar", true),
			array($element4, "hello", false),

			array($element5, "foo", true),
			array($element5, "bar", true),
			array($element5, "hello", true),
			array($element5, "world", false),
		);
	}

	/**
	 * @dataProvider setStandardDataProvider
	 */
	public function testSetStandard($element, $standard, $expected) {
		Element::setStandard($standard);
		$result = (string) $element;
		$this->assertSame($expected, $result);
	}

	public function setStandardDataProvider() {
		$element1 = new Element("div");
		$element2 = new Element("img");
		$element3 = new Element("meta");

		return array(
			array($element1, Element::STANDARD_HTML_5, '<div></div>'),
			array($element2, Element::STANDARD_HTML_5, '<img>'),
			array($element3, Element::STANDARD_HTML_5, '<meta>'),

			array($element1, Element::STANDARD_XHTML_1_0, '<div></div>'),
			array($element2, Element::STANDARD_XHTML_1_0, '<img></img>'),
			array($element3, Element::STANDARD_XHTML_1_0, '<meta></meta>'),
		);
	}
}
?>