<?php
if (!class_exists("Session")) {
	include(CORE_DIR . DS . "session.php");
}

class SessionHelper extends Session {
	public function flash($key = "flash") {
		if ($this->read("Message." . $key) !== null) {
			echo $this->read("Message." . $key);
			$this->delete("Message." . $key);
		}
	}
}
?>