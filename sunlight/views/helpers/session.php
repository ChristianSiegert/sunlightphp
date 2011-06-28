<?php
namespace Views\Helpers;

class Session extends Helper {
	/**
	 * Contains the session data.
	 * @var array
	 */
	public $data = array();

	public function __construct($instanceName = "default") {
		parent::__construct($instanceName, get_called_class());
		$this->data =& $_SESSION;
	}

	public function flash($key = "flash") {
		if (isset($this->data["messages"][$key])) {
			echo $this->data["messages"][$key];
			unset($this->data["messages"][$key]);
		}
	}
}
?>