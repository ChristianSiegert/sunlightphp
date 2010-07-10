<?php
class View {
	private $controller;

	public $data;
	public $helpers;
	public $params;
	public $passedVariables;
	public $validationErrors;

	public $helperObjects = array();

	public $pageTitle = "";
	public $pageKeywords = "";

	function __construct(&$controller) {
		$this->controller = $controller;
		$this->data = $controller->data;
		$this->helpers = $controller->helpers;
		$this->params = $controller->params;
		$this->passedVariables = $controller->passedVariables;
		$this->validationErrors = $controller->validationErrors;

		$this->loadHelpers();
	}

	private function loadHelpers() {
		// Include core helper file
		include(CORE_DIR . DS . "views" . DS . "helper.php");

		for ($i = 0; $i < count($this->helpers); $i++) {
			// Include helper file
			include(CORE_DIR . DS . "views" . DS . "helpers" . DS . strtolower($this->helpers[$i]) . ".php");

			// Instantiate helper
			$helperClassName = $this->helpers[$i] . "Helper";
			$helperObject = ${strtolower($this->helpers[$i])} = new $helperClassName($this);

			// Queue all helpers this helper requires for loading
			if (isset($helperObject->helpers)) {
				$this->helpers = array_unique(array_merge($this->helpers, $helperObject->helpers));
			}

			// Make helper accessible to the view
			$this->helperObjects = array_merge($this->helperObjects, array(
				strtolower($this->helpers[$i]) => $helperObject
			));
		}
	}

	public function renderAction() {
		// Make passed variables available to the view
		foreach ($this->passedVariables as $variableName => $variableValue) {
			$$variableName = $variableValue;
		}

		// Make helpers available to the view
		foreach ($this->helperObjects as $helperName => $helperObject) {
			$$helperName = $helperObject;
		}

		// Filename of the view
		$view = DS . "views" . DS . $this->params["controller"] . DS . str_replace("-", "_", $this->params["action"]) . ".stp";

		// Start buffering output
		ob_start();

		// Load view file
		if (file_exists(APP_DIR . $view)) {
			include(APP_DIR . $view);
		} elseif (file_exists(CORE_DIR . $view)) {
			include(CORE_DIR . $view);
		} else {
			if (Config::read("debug") > 0) {
				$this->controller->Session->setFlash("View $view does not exist.", "flash", array("class" => "flash-error-message"));
			}
		}

		// End buffering output
		$contentForLayout = ob_get_contents();
		ob_end_clean();

		return $contentForLayout;
	}

	public function renderLayout($contentForLayout) {
		// Make helpers available to the layout
		foreach ($this->helperObjects as $helperName => $helperObject) {
			$$helperName = $helperObject;
		}

		// Buffer output of the layout file
		ob_start();
		include(APP_DIR . DS . "views" . DS . "layouts" . DS . "default.stp");
		$renderedLayout = ob_get_contents();
		ob_end_clean();

		// Remove whitespace between HTML tags
		$renderedLayout = preg_replace("#>\s+<#", "><", $renderedLayout);

		return $renderedLayout;
	}
}
?>