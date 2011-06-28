<?php
namespace Views;

use Libraries\Config;
use Libraries\Object;

class View extends Object {
	protected $controller;

	public $data;
	public $params;
	public $assignedVariables;
	public $validationErrors;
	public $view;

	public $pageTitle = "";
	public $pageKeywords = "";

	public function __construct(&$controller) {
		parent::__construct();

		$this->controller = $controller;
		$this->data = $controller->data;
		$this->params = $controller->params;
		$this->assignedVariables = $controller->assignedVariables;
		$this->validationErrors = $controller->validationErrors;
		$this->view = $controller->view;
	}

	public function renderAction() {
		// Make assigned variables available to the view
		extract($this->assignedVariables);

		// Filename of the view
		$viewFile = DS . "views" . DS . str_replace("-", "_", mb_convert_case($this->params["controller"], MB_CASE_LOWER)) . DS . (empty($this->view) ? str_replace("-", "_", $this->params["action"]) : $this->view) . ".stp";

		// Start buffering output
		ob_start();

		// Load view file
		if (is_file(APP_DIR . $viewFile)) {
			require APP_DIR . $viewFile;
		} elseif (is_file(CORE_DIR . $viewFile)) {
			require CORE_DIR . $viewFile;
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
		// Make assigned variables available to the layout
		extract($this->assignedVariables);

		// Buffer output of the layout file
		ob_start();
		require APP_DIR . DS . "views" . DS . "layouts" . DS . "default.stp";
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