<?php
class Session {
	public function initialize() {
		$this->start();
	}

	public function start() {
		ini_set("session.name", SESSION_NAME);
		ini_set("session.cookie_path", BASE_URL . DS);
		ini_set("session.cookie_httponly", true);
		ini_set("session.cookie_lifetime", SESSION_MAX_LIFETIME);
		ini_set("session.gc_maxlifetime", SESSION_MAX_LIFETIME);
		ini_set("session.gc_probability", 0);
		ini_set("session.save_path", APP_DIR . DS . "tmp" . DS . "sessions");
		#ini_set("session.hash_function", "sha1");

		session_start();
	}

	public function destroy() {
		session_destroy();
	}

	/**
	 * Returns session data.
	 *
	 * @param string $key
	 * @return mixed Returns string if $key exists, returns null if $key does not exist, returns array containg whole session data if no $key is specified.
	 */
	public function read($key = null) {
		if ($key !== null) {
			if (isset($_SESSION[$key])) {
				return $_SESSION[$key];
			}
		} else {
			return $_SESSION;
		}
	}

	public function write($key, $data) {
		$_SESSION[$key] = $data;
	}

	public function delete($key) {
		unset($_SESSION[$key]);
	}
}
?>