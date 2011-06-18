<?php
namespace Shells;

class Shell {
	public $params;

	public $validationErrors;

	public function __construct($params) {
		$this->params = $params;
	}

	public function beforeFilter() {

	}
}
?>