<?php
namespace Console;

use Libraries\String;

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
		if (!preg_match('/^[a-z]+$/', $this->params["shell"])) {
			exit("Shell must be called in lower-case dash notation, e.g. 'rental-periods'.\n");
		}

		if (!preg_match('/^[a-z\-]+$/', $this->params["action"])) {
			exit("Action must be in lower-case dash notation, e.g. 'list-all'.\n");
		}

		// Include custom shell file
		$customShellFile = DS . "shells" . DS . $this->params["shell"] . "_shell.php";

		if (!is_file(APP_DIR . $customShellFile)) {
			exit("Shell $customShellFile does not exist.\n");
		}

		$shellClassName = "Shells\\" . ucfirst($this->params["shell"]) . "Shell";
		$shell = new $shellClassName($this->params);

		$methodName = String::dashToCamelCase($this->params["action"]);

		if (!method_exists($shell, $methodName)) {
			exit("Method '$methodName' does not exist in $shellClassName.\n");
		}

		$shell->beforeFilter();

		// Execute action
		call_user_func_array(array($shell, $methodName),  $this->params["passed"]);
	}
}
?>