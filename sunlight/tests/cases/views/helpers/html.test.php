<?php
use Views\Helpers\Html;

require CORE_DIR . DS . "libraries" . DS . "element.php";
require CORE_DIR . DS . "libraries" . DS . "object.php";
require CORE_DIR . DS . "views" . DS . "helpers" . DS . "helper.php";
require CORE_DIR . DS . "views" . DS . "helpers" . DS . "html.php";

class HtmlTest extends PHPUnit_Framework_TestCase {
	public function testLink() {
		$element = Html::link("Hello");
		$expectedResult = '<a>Hello</a>';
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::link("Hello", "http://example.com");
		$expectedResult = '<a href="http://example.com">Hello</a>';
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::link("Hello", "http://example.com", array("foo" => "bar"));
		$expectedResult = '<a foo="bar" href="http://example.com">Hello</a>';
		$this->assertSame($expectedResult, (string) $element);
	}

	public function testImage() {
		$element = Html::image("http://example.com/foo.png");
		$expectedResult = '<img alt="" src="http://example.com/foo.png"></img>';
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::image("http://example.com/foo.png", "Foo bar");
		$expectedResult = '<img alt="Foo bar" src="http://example.com/foo.png"></img>';
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::image("http://example.com/foo.png", "Foo bar", array("foo" => "bar"));
		$expectedResult = '<img foo="bar" alt="Foo bar" src="http://example.com/foo.png"></img>';
		$this->assertSame($expectedResult, (string) $element);
	}

	public function testIcon() {
		$element = Html::icon("http://example.com/foo.png");
		$expectedResult = '<link href="http://example.com/foo.png" rel="shortcut icon" type="image/x-icon"></link>';
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::icon("http://example.com/foo.png", array("foo" => "bar"));
		$expectedResult = '<link foo="bar" href="http://example.com/foo.png" rel="shortcut icon" type="image/x-icon"></link>';
		$this->assertSame($expectedResult, (string) $element);
	}

	public function testScript() {
		$element = Html::script("Foo bar");
		$expectedResult = "<script type=\"text/javascript\">//<![CDATA[\nFoo bar\n//]]></script>";
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::script("Foo bar", array("foo" => "bar"));
		$expectedResult = "<script foo=\"bar\" type=\"text/javascript\">//<![CDATA[\nFoo bar\n//]]></script>";
		$this->assertSame($expectedResult, (string) $element);
	}

	public function testScriptLink() {
		$element = Html::scriptLink("http://example.com/foo.js");
		$expectedResult = '<script src="http://example.com/foo.js" type="text/javascript"></script>';
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::scriptLink("http://example.com/foo.js", array("foo" => "bar"));
		$expectedResult = '<script foo="bar" src="http://example.com/foo.js" type="text/javascript"></script>';
		$this->assertSame($expectedResult, (string) $element);
	}

	public function testAtom() {
		$element = Html::atom("Foo bar", "http://example.com/foo.xml");
		$expectedResult = '<link href="http://example.com/foo.xml" rel="alternate" title="Foo bar" type="application/atom+xml"></link>';
		$this->assertSame($expectedResult, (string) $element);

		$element = Html::atom("Foo bar", "http://example.com/foo.xml", array("foo" => "bar"));
		$expectedResult = '<link foo="bar" href="http://example.com/foo.xml" rel="alternate" title="Foo bar" type="application/atom+xml"></link>';
		$this->assertSame($expectedResult, (string) $element);
	}
}
?>