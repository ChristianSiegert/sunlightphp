<?php
use Libraries\String;

require CORE_DIR . DS . "libraries" . DS . "string.php";

class StringDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider camelCaseToLowerCaseUnderscoreDataProvider
	 */
	public function testCamelCaseToLowerCaseUnderscore($input, $expected) {
		$result = String::camelCaseToLowerCaseUnderscore($input);
		$this->assertSame($expected, $result);
	}

	public function camelCaseToLowerCaseUnderscoreDataProvider() {
		return array(
			array("foo", "foo"),
			array("Foo", "foo"),
			array("fooBar", "foo_bar"),
			array("FooBar", "foo_bar"),
		);
	}

	/**
	 * @dataProvider camelCaseToLowerCaseDashDataProvider
	 */
	public function testCamelCaseToLowerCaseDash($input, $expected) {
		$result = String::camelCaseToLowerCaseDash($input);
		$this->assertSame($expected, $result);
	}

	public function camelCaseToLowerCaseDashDataProvider() {
		return array(
			array("foo", "foo"),
			array("Foo", "foo"),
			array("fooBar", "foo-bar"),
			array("FooBar", "foo-bar"),
		);
	}

	/**
	 * @dataProvider dashToCamelCaseDataProvider
	 */
	public function testdashToCamelCase($input, $expected) {
		$result = String::dashToCamelCase($input);
		$this->assertSame($expected, $result);
	}

	public function dashToCamelCaseDataProvider() {
		return array(
			array("foo", "foo"),
			array("-foo", "-foo"),
			array("foo-", "foo-"),
			array("-foo-", "-foo-"),
			array("foo-bar", "fooBar"),
			array("-foo-bar", "-fooBar"),
			array("foo-bar-", "fooBar-"),
			array("-foo-bar-", "-fooBar-"),

			array("Foo", "foo"),
			array("-Foo", "-foo"),
			array("Foo-", "foo-"),
			array("-Foo-", "-foo-"),
			array("Foo-Bar", "fooBar"),
			array("-Foo-Bar", "-fooBar"),
			array("Foo-Bar-", "fooBar-"),
			array("-Foo-Bar-", "-fooBar-"),

			array("123", "123"),
			array("-123", "-123"),
			array("123-", "123-"),
			array("-123-", "-123-"),
			array("123-bar", "123Bar"),
			array("-123-bar", "-123Bar"),
			array("123-bar-", "123Bar-"),
			array("-123-bar-", "-123Bar-"),

			array("foo-123", "foo123"),
			array("-foo-123", "-foo123"),
			array("foo-123-", "foo123-"),
			array("-foo-123-", "-foo123-"),
		);
	}
}
?>