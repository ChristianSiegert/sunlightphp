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
		if (!is_array($url)) {
			return $url;
		}

		$cacheKey = "router:url:" . serialize($url);
		$string = Cache::fetch($cacheKey);

		if ($string !== false) {
			return $string;
		}

		$string = BASE_URL;

		// Concatenate all passed values
		$pass = "";

		foreach ($url as $key => $value) {
			if ($key !== "controller" && $key !== "action") {
				$pass .= "/$value";
			}
		}

		// Set controller
		if (isset($url["controller"])) {
			$string .= "/" . $url["controller"];
		} else {
			$string .= "/" . Router::$params["controller"];
		}

		// Set action if necessary
		if ($pass === "") {
			if (isset($url["action"])) {
				if ($url["action"] !== "index") {
					$string .= "/" . $url["action"];
				}
			}
		} else {
			if (isset($url["action"])) {
				$string .= "/" . $url["action"] . $pass;
			} else {
				$string .= "/" . Router::$params["action"] . $pass;
			}
		}

		Cache::store($cacheKey, $string);
		return $string;
	}
}
?>