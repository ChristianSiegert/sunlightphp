<?php
namespace Console;

class ShellDispatcher {
	public $params = array(
		"controller" => "",
		"action" => "",
		"passed" => array(),
		"named" => array()
	);

	public function parseParams() {
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
					$this->params["passed"][] = $param;
				}
			}
		}

		if (empty($this->params["action"])) {
			$this->params["action"] = "index";
		}
	}

	public function dispatch() {
		// Include custom shell file
		$customShellFile = DS . "shells" . DS . $this->params["shell"] . "_shell.php";

		if (preg_match('/^[a-z]+$/', $this->params["shell"])
				&& is_file(APP_DIR . $customShellFile)) {
			require APP_DIR . $customShellFile;

			$shellClassName = "Shells\\" . ucfirst($this->params["shell"]) . "Shell";
			$shell = new $shellClassName($this->params);

			$methodName = str_replace("-", "_", $this->params["action"]);

			if (preg_match('/^[a-z-]+$/', $this->params["action"])
					&& method_exists($shell, $methodName)) {
				$shell->beforeFilter();

				// Execute action
				call_user_func_array(array($shell, $methodName),  $this->params["passed"]);
			} else {
				print("Method $methodName() does not exist in $shellClassName.\n");
			}
		} else {
			print("Shell $customShellFile does not exist.\n");
		}
	}
}
?>