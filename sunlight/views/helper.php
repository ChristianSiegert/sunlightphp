<?php
class Helper {
	public $data;
	public $helpers = array();
	public $params;
	public $validationErrors;
	public $view;

	public function __construct(&$view) {
		$this->data = $view->data;
		$this->params = $view->params;
		$this->validationErrors = $view->validationErrors;
		$this->view = $view;
	}
}
?>