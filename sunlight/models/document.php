<?php
class Document extends CouchDbDocument {
	/**
	 * Constructs the object and sets the database.
	 * @param string $id Document _id
	 * @param string $revision Document _rev
	 */
	public function __construct($id, $revision = "") {
		parent::__construct($id, $revision);
		$this->setDatabase(DATABASE_HOST, DATABASE_NAME);
	}
}
?>