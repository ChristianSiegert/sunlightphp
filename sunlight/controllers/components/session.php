<?php
namespace Controllers\Components;

use Libraries\Element;

class Session {
	/**
	 * Contains the session data.
	 * @var array
	 */
	public $data = array();

	public function initialize() {
		ini_set("session.name", SESSION_NAME);
		ini_set("session.cookie_path", BASE_URL . "/");
		ini_set("session.cookie_httponly", true);
		ini_set("session.cookie_lifetime", SESSION_MAX_LIFETIME);
		ini_set("session.gc_maxlifetime", SESSION_MAX_LIFETIME);
		ini_set("session.gc_probability", 0);
		ini_set("session.save_path", APP_DIR . DS . "tmp" . DS . "sessions");
		#ini_set("session.hash_function", "sha1");

		session_start();
		$this->data =& $_SESSION;
	}

	public function end() {
		session_destroy();
	}

	public function setFlash($message, $key = "flash", $attributes = array()) {
		$element = new Element("div", $attributes, $message);

		if (!isset($element->class)) {
			$element->class = "flash-message";
		}

		$this->data["messages"][$key] = $element->toString();
	}
}
?>