<?php
namespace Libraries;

class Config {
	protected static $config = array();

	public static function read($key) {
		return self::$config[$key];
	}

	public static function write($key, $value) {
		self::$config[$key] = $value;
	}
}
?>