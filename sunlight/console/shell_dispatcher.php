<?php
class ShellDispatcher {
	public $params;

	public function parseParams() {
		// Initialize "pass" and "named" field
		$this->params["pass"] = array();
		$this->params["named"] = array();

		// Extract shell and action from arguments if possible
		foreach ($_SERVER["argv"] as $i => $param) {
			if ($i === 1) {
				$this->params["shell"] = $param;
			} elseif ($i === 2) {
				$this->params["action"] = $param;
			} elseif ($i >= 3) {
				if (preg_match("/^([^:]+):(.*)$/", $param, $match)) {
					$this->params["named"][$match[1]] = $match[2];
				} else {
					$this->params["pass"][] = $param;
				}
			}
		}

		if (empty($this->params["action"])) {
			$this->params["action"] = "index";
		}
	}

	public function dispatch() {
		// Include shell file
		include(CORE_DIR . DS . "shells" . DS . "shell.php");

		// Include app shell file
		$appShellFile = DS . "shells" . DS . "app_shell.php";

		if (is_file(APP_DIR . $appShellFile)) {
			include(APP_DIR . $appShellFile);
		} else {
			include(CORE_DIR . $appShellFile);
		}

		// Include custom shell file
		$customShellFile = APP_DIR . DS . "shells" . DS . $this->params["shell"] . "_shell.php";

		if (preg_match('/^[a-z]+$/', $this->params["shell"])
				&& is_file($customShellFile)) {
			include($customShellFile);

			$shellClassName = ucfirst($this->params["shell"]) . "Shell";
			$shell = new $shellClassName($this->params);

			$methodName = str_replace("-", "_", $this->params["action"]);

			if (preg_match('/^[a-z-]+$/', $this->params["action"])
					&& method_exists($shell, $methodName)) {
				$shell->beforeFilter();

				if ($shell->loadModel) {
					include(CORE_DIR . DS . "inflector.php");
					$shell->loadModel();
				}

				// Execute action
				call_user_func_array(array($shell, $methodName),  $this->params["pass"]);
			} else {
				print("Method $methodName() does not exist in $shellClassName.\n");
			}
		} else {
			print("Shell $customShellFile does not exist.\n");
		}
	}
}
?>