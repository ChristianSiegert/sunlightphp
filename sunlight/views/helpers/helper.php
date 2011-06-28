<?php
namespace Views\Helpers;

use Libraries\Object;

class Helper extends Object {
	/**
	 * View object.
	 * @var \Views\View
	 */
	public $view;

	public $data;
	public $params;
	public $validationErrors;

	public function __construct($instanceName = "default") {
		parent::__construct($instanceName, get_called_class());

		if (!$view = \Views\View::getInstance()) {
			throw new \Exception("Could not get instance of class \\Views\\View.");
		}

		$this->view = $view;
		$this->data = $view->data;
		$this->params = $view->params;
		$this->validationErrors = $view->validationErrors;
	}
}
?>