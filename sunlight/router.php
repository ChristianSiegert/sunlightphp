<?php
class Router {
	public static $params;

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
}
?>