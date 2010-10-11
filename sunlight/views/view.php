<?php
class View {
	private $controller;

	public $data;
	public $helpers;
	public $params;
	public $passedVariables;
	public $validationErrors;
	public $view;

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
		$this->view = $controller->view;

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

			// Queue all helpers that this helper requires for loading
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
		// Make passed variables and helpers available to the view
		extract($this->passedVariables);
		extract($this->helperObjects);

		// Filename of the view
		$viewFile = DS . "views" . DS . $this->params["controller"] . DS . (empty($this->view) ? str_replace("-", "_", $this->params["action"]) : $this->view) . ".stp";

		// Start buffering output
		ob_start();

		// Load view file
		if (is_file(APP_DIR . $viewFile)) {
			include(APP_DIR . $viewFile);
		} elseif (is_file(CORE_DIR . $viewFile)) {
			include(CORE_DIR . $viewFile);
		} else {
			if (Config::read("debug") > 0) {
				$this->controller->Session->setFlash("View $viewFile does not exist.", "flash", array("class" => "flash-error-message"));
			}
		}

		// End buffering output
		$contentForLayout = ob_get_contents();
		ob_end_clean();

		return $contentForLayout;
	}

	public function renderLayout($contentForLayout, $removeWhitespace) {
		// Make helpers available to the layout
		extract($this->helperObjects);

		// Buffer output of the layout file
		ob_start();
		include(APP_DIR . DS . "views" . DS . "layouts" . DS . "default.stp");
		$renderedLayout = ob_get_contents();
		ob_end_clean();

		// Remove whitespace between HTML tags
		if ($removeWhitespace) {
			$renderedLayout = preg_replace('#>\s+<#', "><", $renderedLayout);
		}

		return $renderedLayout;
	}
}
?>