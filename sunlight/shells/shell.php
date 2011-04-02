<?php
class Shell {
	public $models = array();

	public $params;

	public $validationErrors;

	public function __construct($params) {
		$this->params = $params;
	}

	public function beforeFilter() {

	}
}
?>