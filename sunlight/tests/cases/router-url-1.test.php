<?php
use Libraries\Router as Router;

require CORE_DIR . DS . "libraries" . DS . "router.php";

class RouterUrlDataTest1 extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		if (!defined("BASE_URL")) {
			define("BASE_URL", "/sunlightphp");
		}
	}

	/**
	 * @dataProvider urlDataProvider
	 */
	public function testUrl($input, $expected) {
		Router::$params = $input["params"];
		$result = Router::url($input["url"], $input["makeAbsolute"]);
		$this->assertSame($expected, $result);
	}

	public function urlDataProvider() {
		return array(
			// Called from controller "users" and method "index"
			array(
				array(
					"url" => array(),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("controller" => "users"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("action" => "index"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/index/by-name"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/index/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "by-name", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/index/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2, "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/index/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2, "by-name", "compact" => "true"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/index/by-name/page:2/compact:true"
			),

			// Called from controller "users" and method "index"
			array(
				array(
					"url" => array(),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("controller" => "users"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("action" => "list"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/list"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "list"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/list"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "list", "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/list/by-name"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "list", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/list/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "list", "by-name", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/list/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "list", "page" => 2, "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/list/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "list", "page" => 2, "by-name", "compact" => "true"),
					"makeAbsolute" => false,
					"params" => array("controller" => "users", "action" => "index")
				),
				"/sunlightphp/users/list/by-name/page:2/compact:true"
			),


			// Called from controller "pictures" and method "index"
			array(
				array(
					"url" => array(),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/pictures"
			),
			array(
				array(
					"url" => array("controller" => "users"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("action" => "index"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/pictures"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/users/index/by-name"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/users/index/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "by-name", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/users/index/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2, "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/users/index/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2, "by-name", "compact" => "true"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "index")
				),
				"/sunlightphp/users/index/by-name/page:2/compact:true"
			),


			// Called from controller "pictures" and method "list"
			array(
				array(
					"url" => array(),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/pictures/list"
			),
			array(
				array(
					"url" => array("controller" => "users"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("action" => "index"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/pictures"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/users"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/users/index/by-name"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/users/index/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "by-name", "page" => 2),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/users/index/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2, "by-name"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/users/index/by-name/page:2"
			),
			array(
				array(
					"url" => array("controller" => "users", "action" => "index", "page" => 2, "by-name", "compact" => "true"),
					"makeAbsolute" => false,
					"params" => array("controller" => "pictures", "action" => "list")
				),
				"/sunlightphp/users/index/by-name/page:2/compact:true"
			),
		);
	}
}
?>