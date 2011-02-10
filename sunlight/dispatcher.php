<?php
class Dispatcher {
	public $params = array(
		"url" => array(),
		"controller" => "",
		"action" => "",
		"passed" => array(),
		"named" => array()
	);

	/**
	 * Parses URL passed in $_GET["url"].
	 *
	 * Does not evaluate the hash tag since browsers never send it to servers.
	 */
	public function parseParams() {
		if (!isset($_GET["url"])) {
			$_GET["url"] = "";
		}

		// Dump all $_GET content into $this->params["url"]
		$this->params["url"] = $_GET;

		// Prepend slash to URL
		$url = $this->params["url"]["url"] = "/" . $this->params["url"]["url"];

		// Get route for current URL
		$route = Router::getRoute($url);

		// Extract controller and action from URL if possible
		$params = explode("/", trim($url, "/"));

		foreach ($params as $i => $param) {
			if ($i === 0) {
				$this->params["controller"] = isset($route["controller"]) ? $route["controller"] : $param;
			} elseif ($i === 1) {
				$this->params["action"] = isset($route["action"]) ? $route["action"] : $param;
			} else {
				if (preg_match("/^([^:]+):(.*)$/", $param, $match)) {
					$this->params["named"][$match[1]] = $match[2];
				} else {
					$this->params["passed"][] = $param;
				}
			}
		}

		if (empty($this->params["action"])) {
			$this->params["action"] = isset($route["action"]) ? $route["action"] : "index";
		}

		unset($_GET["url"]);
	}

	public function dispatch() {
		// Include controller file
		include(CORE_DIR . DS . "controllers" . DS . "controller.php");

		// Include app controller file
		$appControllerFile = DS . "controllers" . DS . "app_controller.php";
		include(is_file(APP_DIR . $appControllerFile) ? APP_DIR . $appControllerFile : CORE_DIR . $appControllerFile);

		// Include custom controller file
		$customControllerFile = DS . "controllers" . DS . $this->params["controller"] . "_controller.php";

		if (preg_match('/^[a-z]+$/', $this->params["controller"])
				&& is_file(APP_DIR . $customControllerFile)) {
			include(APP_DIR . $customControllerFile);

			// Create controller object
			$controllerClassName = ucfirst($this->params["controller"]) . "Controller";
			$controller = new $controllerClassName($this->params);

			$methodName = str_replace("-", "_", $this->params["action"]);

			if (!preg_match('/^[a-z-]+$/', $this->params["action"])
					|| !method_exists($controller, $methodName)) {
				$errorMessage = "Method $methodName() does not exist in $controllerClassName.";
			}
		} else {
			$errorMessage = "Controller $customControllerFile does not exist.";
		}

		if (isset($errorMessage)) {
			header("HTTP/1.1 404 Not found");
			include(CORE_DIR . DS . "controllers" . DS . "errors_controller.php");

			$this->params["controller"] = "errors";
			$this->params["action"] = "error-404";

			$controller = new ErrorsController($this->params);
			$methodName = str_replace("-", "_", $this->params["action"]);
		}

		if ($controller->cacheActions && $controller->autoRender && Config::read("debug") === 0) {
			$cacheKey = "dispatcher:dispatch:" . $this->params["controller"] . ":" . $this->params["action"] . ":" . serialize($this->params["passed"]);
			$page = Cache::fetch($cacheKey);

			if ($page !== false) {
				echo $page;
				return;
			}
		}

		$controller->loadComponents();
		$controller->beforeFilter();
		$controller->startUpComponents();
		$controller->loadModels();

		// Execute action
		call_user_func_array(array($controller, $methodName), $this->params["passed"]);

		if (Config::read("debug") > 0 && isset($errorMessage)) {
			$controller->Session->setFlash($errorMessage, "flash", array("class" => "flash-error-message"));
		}

		if ($controller->autoRender) {
			$page = $controller->render();

			if ($controller->cacheActions && Config::read("debug") === 0) {
				Cache::store($cacheKey, $page);
			}

			echo $page;
		}
	}
}
?>