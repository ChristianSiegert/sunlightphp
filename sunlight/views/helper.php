<?php
class Helper {
	public $data;
	public $helpers = array();
	public $params;
	public $validationErrors;
	public $view;

	public function __construct(&$view) {
		$this->data = $view->data;
		$this->params = $view->params;
		$this->validationErrors = $view->validationErrors;
		$this->view = $view;
	}

	/**
	 * Creates URL.
	 *
	 * @param array $url
	 * @return string
	 */
	public function url($url) {
		$cacheKey = "helper:url:" . serialize($url);
		$string = Cache::fetch($cacheKey);

		if ($string !== false) {
			return $string;
		}

		$string = BASE_URL;

		if (isset($url["controller"])) {
			$string .= "/" . $url["controller"];
		} else {
			$string .= "/" . $this->params["controller"];
		}

		if (isset($url["action"])) {
			if ($url["action"] !== "index") {
				$string .= "/" . $url["action"];
			}
		}

		foreach ($url as $key => $value) {
			if ($key !== "controller" && $key !== "action") {
				$string .= "/$value";
			}
		}

		Cache::store($cacheKey, $string);

		return $string;
	}
}
?>