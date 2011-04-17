<?php
namespace Models;

class View extends CouchDb\CouchDbView {
	/**
	 * Constructs the object and sets the database.
	 * @param string $designName
	 * @param string $viewName
	 */
	public function __construct($designName = "", $viewName = "") {
		parent::__construct($designName, $viewName);
		$this->setDatabase(DATABASE_HOST, DATABASE_NAME);
	}
}
?>