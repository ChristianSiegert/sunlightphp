<?php
class Dispatcher {
	private $params;

	public function parseParams() {
		$cacheKey = "dispatcher:parseParams:" . serialize($_GET);
		$params = Cache::fetch($cacheKey, "apcOnly");

		if ($params !== false) {
			$this->params = unserialize($params);
		} else {
			// Dump all $_GET content into $this->params["url"]
			$this->params["url"] = $_GET;

			// Prepend slash to URL
			if (!isset($this->params["url"]["url"])) {
				$this->params["url"]["url"] = "/";
			} else {
				$this->params["url"]["url"] = "/" . $this->params["url"]["url"];
			}

			// Initialize "pass" field
			$this->params["pass"] = array();

			// Extract controller and action from URL if possible
			$params = explode("/", $this->params["url"]["url"]);
			$i = 0;

			foreach ($params as $param) {
				if ($param !== "") {
					if ($i === 0) {
						if (preg_match("/^[a-z]+$/", $param) === 1) {
							$this->params["controller"] = $param;
						} else {
							$this->params["controller"] = "errors";
							$this->params["action"] = "error-404";
							break;
						}
					} elseif ($i === 1) {
						if (preg_match("/^[a-z-]+$/", $param) === 1) {
							$this->params["action"] = $param;
						} else {
							$this->params["controller"] = "errors";
							$this->params["action"] = "error-404";
							break;
						}
					} else {
						$this->params["pass"][$i-2] = $param;
					}
					$i++;
				}
			}

			// Set default controller and/or action if necessary
			if (empty($this->params["controller"])) {
				$this->params["controller"] = "pages";
				$this->params["action"] = "home";
			} elseif (empty($this->params["action"])) {
				$this->params["action"] = "index";
			}

			Cache::store($cacheKey, serialize($this->params), 60, "apcOnly");
		}
	}

	public function dispatch() {
		// Include controller file
		include(CORE_DIR . DS . "controllers" . DS . "controller.php");

		// Include app controller file
		$appControllerFile = DS . "controllers" . DS . "app_controller.php";

		if (file_exists(APP_DIR . $appControllerFile)) {
			include(APP_DIR . $appControllerFile);
		} else {
			include(CORE_DIR . $appControllerFile);
		}

		// Include custom controller file
		$customControllerFile = DS . "controllers" . DS . $this->params["controller"] . "_controller.php";

		if (file_exists(APP_DIR . $customControllerFile)
				&& $this->params["controller"] !== "errors") {
			include(APP_DIR . $customControllerFile);

			// Create controller object
			$controllerClassName = ucfirst($this->params["controller"]) . "Controller";
			$controller = new $controllerClassName($this->params);

			$methodName = str_replace("-", "_", $controller->params["action"]);

			if (!method_exists($controller, $methodName)) {
				$errorMessage = "Method $methodName() does not exist in " . $controllerClassName . ".";
			}
		} else {
			$errorMessage = "Controller $customControllerFile does not exist.";
		}

		if (isset($errorMessage)) {
			header("HTTP/1.1 404 Page not found");
			include(CORE_DIR . DS . "controllers" . DS . "errors_controller.php");

			$this->params["controller"] = "errors";
			$this->params["action"] = "error-404";

			$controller = new ErrorsController($this->params, $errorMessage);
			$methodName = str_replace("-", "_", $controller->params["action"]);
		}

		if ($controller->autoRender && $controller->cacheActions) {
			$cacheKey = "dispatcher:dispatch:" . $controller->params["controller"] . ":" . $controller->params["action"] . ":" . serialize($controller->params["pass"]);
			$page = Cache::fetch($cacheKey);

			if ($page !== false) {
				echo $page;
				return;
			}
		}

		include(CORE_DIR . DS . "inflector.php");

		$controller->loadComponents();
		$controller->beforeFilter();
		$controller->startUpComponents();

		if ($controller->loadModel) {
			$controller->loadModels();
		}

		// Execute action
		call_user_func_array(array($controller, $methodName), $this->params["pass"]);

		if (Config::read("debug") > 0 && isset($errorMessage)) {
			$controller->Session->setFlash($errorMessage, "flash", array("class" => "flash-error-message"));
		}

		if ($controller->autoRender) {
			$page = $controller->render();

			if ($controller->cacheActions) {
				Cache::store($cacheKey, $page);
			}

			echo $page;
		}
	}
}
?>