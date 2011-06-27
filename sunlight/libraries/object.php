<?php
namespace Libraries;

class Object {
	protected static $instances = array();

	/**
	 * Constructs the object. You can give the instance a name of your choice.
	 * Use ::getInstance() to retrieve that instance.
	 * @param string $instanceName
	 */
	public function __construct($instanceName = "default") {
		self::$instances[get_called_class()][$instanceName] = $this;
	}

	/**
	 * Returns an instance of the class this method is called from. If no
	 * instance exists, false is returned.
	 * @param string $instanceName
	 * @return object|boolean Object if instance exists, or false
	 */
	public static function getInstance($instanceName = "default") {
		$className = get_called_class();

		if (isset(self::$instances[$className]) && isset(self::$instances[$className][$instanceName])) {
			return self::$instances[$className][$instanceName];
		}

		return false;
	}
}
?>