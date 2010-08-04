<?php
class Dispatcher {
	public $params;

	public function parseParams() {
		$cacheKey = "dispatcher:parseParams:" . serialize($_GET);
		$params = Cache::fetch($cacheKey, "apcOnly");

		if ($params !== false) {
			$this->params = unserialize($params);
		} else {
			if (!isset($_GET["url"])) {
				$_GET["url"] = "";
			}

			// Dump all $_GET content into $this->params["url"]
			$this->params["url"] = $_GET;

			// Prepend slash to URL
			$this->params["url"]["url"] = "/" . $this->params["url"]["url"];

			// Initialize "pass" field
			$this->params["pass"] = array();

			// Extract controller and action from URL if possible
			$params = explode("/", $this->params["url"]["url"]);
			$i = 0;

			foreach ($params as $param) {
				if ($param !== "") {
					if ($i === 0) {
						$this->params["controller"] = $param;
					} elseif ($i === 1) {
						$this->params["action"] = $param;
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

		unset($_GET["url"]);
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

		if (preg_match('/^[a-z]+$/', $this->params["controller"]) === 1
				&& file_exists(APP_DIR . $customControllerFile)) {
			include(APP_DIR . $customControllerFile);

			// Create controller object
			$controllerClassName = ucfirst($this->params["controller"]) . "Controller";
			$controller = new $controllerClassName($this->params);

			$methodName = str_replace("-", "_", $this->params["action"]);

			if (preg_match('/^[a-z-]+$/', $this->params["action"]) === 0
					|| !method_exists($controller, $methodName)) {
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

			$controller = new ErrorsController($this->params);
			$methodName = str_replace("-", "_", $this->params["action"]);
		}

		if ($controller->autoRender && $controller->cacheActions) {
			$cacheKey = "dispatcher:dispatch:" . $this->params["controller"] . ":" . $this->params["action"] . ":" . serialize($this->params["pass"]);
			$page = Cache::fetch($cacheKey);

			if ($page !== false && Config::read("debug") === 0) {
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

			if ($controller->cacheActions && Config::read("debug") === 0) {
				Cache::store($cacheKey, $page);
			}

			echo $page;
		}
	}
}
?>