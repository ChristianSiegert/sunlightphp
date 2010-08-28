<?php
class Router {
	public static $params;

	public static $routes = array();

	/**
	 * Creates a URL.
	 *
	 * @param array|string $url
	 * @return string
	 */
	public static function url($url, $makeAbsolute = false) {
		$string = "";

		// Concatenate all passed values
		$passedValues = "";

		foreach ($url as $key => $value) {
			if ($key !== "controller" && $key !== "action") {
				$passedValues .= "/" . $value;
			}
		}

		// Set action if necessary
		if (isset($url["controller"]) && !isset($url["action"])) {
			$url["action"] = "index";
		} elseif (!isset($url["controller"]) && !isset($url["action"])) {
			$url["action"] = Router::$params["action"];
		}

		// Append action and passed values to URL
		if ($passedValues === "") {
			if ($url["action"] !== "index") {
				$string .= "/" . $url["action"];
			}
		} else {
			$string .= "/" . $url["action"] . $passedValues;
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