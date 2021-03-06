<?php
namespace Libraries;

class Router {
	public static $params;

	public static $routes = array();

	/**
	 * Returns a URL as string after assembling it from a passed array.
	 *
	 * @param array $url
	 * @param boolean $makeAbsolute Set to true to create an absolute URL, starting with http://
	 * @return string
	 */
	public static function url($url, $makeAbsolute = false) {
		// Concatenate all passed and named values
		$namedValues = "";
		$passedValues = "";

		foreach ($url as $key => $value) {
			if ($key !== "controller" && $key !== "action") {
				if (is_string($key)) {
					$namedValues .= "/$key:$value";
				} else {
					$passedValues .= "/$value";
				}
			}
		}

		// Set action if necessary
		if (!isset($url["action"])) {
			$url["action"] = isset($url["controller"]) ? "index" : Router::$params["action"];
		}

		$string = "";

		// Append action and passed values to URL
		if ($passedValues === "" && $namedValues === "") {
			if ($url["action"] !== "index") {
				$string .= "/" . $url["action"];
			}
		} else {
			$string .= "/" . $url["action"] . $passedValues . $namedValues;
		}

		// Set controller if necessary
		if (!isset($url["controller"])) {
			$url["controller"] = Router::$params["controller"];
		}

		// Create first part of URL
		$string = ($makeAbsolute ? "http://" . $_SERVER["HTTP_HOST"] : "") . BASE_URL . "/" . $url["controller"] . $string;

		return $string;
	}

	public static function connect($url, $route) {
		$url = rtrim($url, "/");
		self::$routes[$url] = $route;
	}

	public static function getRoute($url) {
		$url = rtrim($url, "/");
		return isset(self::$routes[$url]) ? self::$routes[$url] : false;
	}
}
?>