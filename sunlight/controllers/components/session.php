<?php
if (!class_exists("Session")) {
	include(CORE_DIR . DS . "session.php");
}

class SessionComponent extends Session {
	public function setFlash($message, $key = "flash", $options = array()) {
		$options["html"] = $message;

		if (!isset($options["class"])) {
			$options["class"] = "flash-message";
		}

		$flashElement = new Element("div", $options);
		$this->write("Message." . $key, $flashElement->toString());
	}
}
?>