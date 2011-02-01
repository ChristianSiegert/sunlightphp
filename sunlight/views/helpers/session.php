<?php
class SessionHelper {
	/**
	 * Contains the session data.
	 * @var array
	 */
	public $data = array();

	public function __construct() {
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