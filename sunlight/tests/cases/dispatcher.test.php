<?php
namespace Libraries;

class Router {
	public static function getRoute() {
		return false;
	}
}

require CORE_DIR . DS . "libraries" . DS . "dispatcher.php";

// Use assertEquals to compare arrays if key order unimportant.

class DispatcherDataTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider parseParamsDataProvider
	 */
	public function testParseParams($input, $expected) {
		$_GET = $input;
		$dispatcher = new Dispatcher();
		$dispatcher->parseParams();
		$result = $dispatcher->params;

		$this->assertEquals($expected, $result);
	}

	public function parseParamsDataProvider() {
		return array(
			array(
				array(),
				array(
					"url" => "/",
					"controller" => "",
					"action" => "index",
					"passed" => array(),
					"named" => array(),
				)
			),
			array(
				array(
					"sunlightphp_url" => "foo"
				),
				array(
					"url" => "/foo",
					"controller" => "foo",
					"action" => "index",
					"passed" => array(),
					"named" => array(),
				)
			),
			array(
				array(
					"sunlightphp_url" => "foo/bar"
				),
				array(
					"url" => "/foo/bar",
					"controller" => "foo",
					"action" => "bar",
					"passed" => array(),
					"named" => array(),
				)
			),
			array(
				array(
					"sunlightphp_url" => "foo/bar/peng"
				),
				array(
					"url" => "/foo/bar/peng",
					"controller" => "foo",
					"action" => "bar",
					"passed" => array("peng"),
					"named" => array(),
				)
			),
			array(
				array(
					"sunlightphp_url" => "foo/bar/cow:moo"
				),
				array(
					"url" => "/foo/bar/cow:moo",
					"controller" => "foo",
					"action" => "bar",
					"passed" => array(),
					"named" => array("cow" => "moo"),
				)
			),
			array(
				array(
					"sunlightphp_url" => "foo/bar/cow:moo/ouch/bird:cheep/count:3/peng"
				),
				array(
					"url" => "/foo/bar/cow:moo/ouch/bird:cheep/count:3/peng",
					"controller" => "foo",
					"action" => "bar",
					"passed" => array("ouch", "peng"),
					"named" => array(
						"cow" => "moo",
						"bird" => "cheep",
						"count" => "3",
					),
				)
			)
		);
	}
}
?>