<?php
namespace Libraries;

class Dispatcher {
	public $params = array(
		"url" => "",
		"controller" => "",
		"action" => "",
		"passed" => array(),
		"named" => array(),
	);

	/**
	 * Fills $this->params.
	 */
	public function parseParams() {
		// Grab the URL Apache passed to SunlightPHP
		$url = isset($_GET["sunlightphp_url"]) ? $_GET["sunlightphp_url"] : "";
		unset($_GET["sunlightphp_url"]);

		// Prepend slash to URL
		$url = $this->params["url"] = "/$url";

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
	}

	public function dispatch($flashMessage = "") {
		if (!preg_match('/^[a-z0-9\-]+$/', $this->params["controller"])) {
			$this->exitWithError("Controller must be called in lower-case dash notation, e.g. 'rental-periods'.");
		}

		if (!preg_match('/^[a-z0-9\-]+$/', $this->params["action"])) {
			$this->exitWithError("Action must be in lower-case dash notation, e.g. 'list-all'.");
		}

		// Include custom controller file
		$customControllerFile = DS . "controllers" . DS . str_replace("-", "_", $this->params["controller"]) . "_controller.php";

		if (!is_file(APP_DIR . $customControllerFile)
				&& !is_file(CORE_DIR . $customControllerFile)) {
			$this->exitWithError("Controller $customControllerFile does not exist.");
		}

		// Create controller object
		$controllerClassName = "Controllers\\" . str_replace("-", "", mb_convert_case($this->params["controller"], MB_CASE_TITLE)) . "Controller";
		$controller = new $controllerClassName($this->params);

		$methodName = String::dashToCamelCase($this->params["action"]);

		if (!method_exists($controller, $methodName)) {
			$this->exitWithError("Method '$methodName()' does not exist in $controllerClassName.");
		}

		// If caching of actions is enabled, retrieve rendered page from cache if possible
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

		// Execute action
		call_user_func_array(array($controller, $methodName), $this->params["passed"]);

		// Display any messages
		if (Config::read("debug") > 0 && $flashMessage) {
			$controller->Session->setFlash($flashMessage, "flash", array("class" => "flash-error-message"));
		}

		// Render page
		if ($controller->autoRender) {
			$page = $controller->render();

			if ($controller->cacheActions && Config::read("debug") === 0) {
				Cache::store($cacheKey, $page);
			}

			echo $page;
		}
	}

	protected function exitWithError($errorMessage) {
		header("HTTP/1.1 404 Not found");

		$this->params["controller"] = "errors";
		$this->params["action"] = "error-404";

		$this->dispatch($errorMessage);
		exit;
	}
}
?>