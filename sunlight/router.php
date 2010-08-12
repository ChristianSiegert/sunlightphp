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
	public static function url($url) {
		// Set controller if necessary
		if (!isset($url["controller"])) {
			$url["controller"] = Router::$params["controller"];
		}

		$cacheKey = "router:url:" . serialize($url);
		$string = Cache::fetch($cacheKey);

		if ($string !== false) {
			return $string;
		}

		// Create first part of URL
		$string = BASE_URL . "/" . $url["controller"];

		// Set action if necessary
		if (!isset($url["action"])) {
			$url["action"] = "index";
		}

		// Concatenate all passed values
		$passedValues = "";

		foreach ($url as $key => $value) {
			if ($key !== "controller" && $key !== "action") {
				$passedValues .= "/" . $value;
			}
		}

		// Append action and passed values to URL
		if ($passedValues === "") {
			if ($url["action"] !== "index") {
				$string .= "/" . $url["action"];
			}
		} else {
			$string .= "/" . $url["action"] . $passedValues;
		}

		Cache::store($cacheKey, $string);
		return $string;
	}

	public static function connect($url, $route) {
		self::$routes[$url] = $route;
	}

	public static function getRoute($url) {
		return isset(self::$routes[$url]) ? self::$routes[$url] : false;
	}
}
?>