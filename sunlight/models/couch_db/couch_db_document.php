<?php
namespace Models\CouchDb;

use \Exception as Exception;
use Libraries\HttpRequest as HttpRequest;

class CouchDbDocument extends CouchDb {
	/**
	 * Contains the actual document.
	 * @var stdClass
	 */
	protected $document;

	/**
	 * Contains the parameters passed in the URL when querying for the document.
	 * @var array
	 */
	protected $options = array();

	/**
	 * Names of the meta fields that are currently supported by CouchDB (as of
	 * CouchDB 1.0.1).
	 * @var string
	 */
	const META_ATTACHMENTS = "_attachments";
	const META_CONFLICTS = "_conflicts";
	const META_DELETED = "_deleted";
	const META_DELETED_CONFLICTS = "_deleted_conflicts";
	const META_REVISIONS = "_revisions";
	const META_REVS_INFO = "_revs_info";

	/**
	 * Constructs the object.
	 * @param string $id Document _id
	 * @param string $revision Document _rev
	 */
	public function __construct($id, $revision = "") {
		$this->document = new \stdClass();
		$this->document->_id = $id;

		if (!empty($revision)) {
			$this->document->_rev = $revision;
		}
	}

	/**
	 * Copies referenced objects to avoid sharing them.
	 */
	public function __clone() {
		$this->document = clone $this->document;
	}

	/**
	 * Returns a document field (only if the read request comes from outside the
	 * class).
	 * @param string $fieldName
	 */
	public function __get($fieldName) {
		return isset($this->document->$fieldName) ? $this->document->$fieldName : null;
	}

	/**
	 * Writes to a document field (only if the write request comes from outside
	 * class). If $fieldValue is an associative array, it is converted to a
	 * stdClass object.
	 * @param string $fieldName
	 * @param mixed $fieldValue
	 */
	public function __set($fieldName, $fieldValue) {
		$this->document->$fieldName = json_decode(json_encode($fieldValue));
	}

	/**
	 * Deletes a document field (only if the delete request comes from outside
	 * the class).
	 * @param string $fieldName
	 */
	public function __unset($fieldName) {
		unset($this->document->$fieldName);
	}

	/**
	 * Checks if a document field is set (only if the isset request comes from
	 * outside the class).
	 * @param string $fieldName
	 * @return boolean
	 */
	public function __isset($fieldName) {
		return isset($this->document->$fieldName);
	}

	/**
	 * Returns the document in JSON format.
	 * @return string Document in JSON format.
	 */
	public function __toString() {
		return json_encode($this->document);
	}

	/**
	 * Fetches the document from the database. (Fetches the most recent one if
	 * _rev is not set.)
 	 * @return CouchDbDocument
	 * @throws Exception
	 */
	public function fetch() {
		$this->requireDatabase();
		$this->requireId();

		if (!empty($this->document->_rev)) {
			$this->options["rev"] = $this->document->_rev;
		}

		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/" . rawurlencode($this->document->_id) . self::encodeOptions($this->options));
		$request->send();

		$request->response = json_decode($request->response);

		if ($request->status === 200) {
			foreach ($request->response as $fieldName => $fieldValue) {
				$this->document->$fieldName = $fieldValue;
			}

			return $this;
		} elseif (isset($this->document->_rev)) {
			throw new Exception(self::describeError($request->response, $this->document->_id, $this->document->_rev));
		} else {
			throw new Exception(self::describeError($request->response, $this->document->_id));
		}
	}

	/**
	 * Saves the document to the database. (Creates a new one in the database if
	 * _rev is not set. Updates an existing one if _rev is set.)
	 *
	 * Automatically adds a field "type" if it does not exist already. Its value
	 * is the document classname.
	 *
	 * @return boolean True if preSave, save and postSave completed successfully, otherwise false
	 * @throws Exception
	 */
	public function save() {
		if (!$this->preSave()) {
			return false;
		}

		$this->requireDatabase();
		$this->requireId();

		if (empty($this->document->type)) {
			$this->document->type = get_class($this);
		}

		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/" . rawurlencode($this->document->_id));
		$request->setMethod("put");
		$request->setData(json_encode($this->document));
		$request->send();

		$request->response = json_decode($request->response);

		if ($request->status === 201) {
			$this->document->_id = $request->response->id;
			$this->document->_rev = $request->response->rev;

			return $this->postSave();
		} else {
			throw new Exception(self::describeError($request->response));
		}
	}

	/**
	 *  Is fired at the beginning of CouchDbDocument::save() and determines
	 *  whether it continues or is aborted.
	 *  @return boolean
	 */
	protected function preSave() {
		return true;
	}

	/**
	 * Is fired at the end of CouchDbDocument::save() and determines its
	 * return value.
	 * @return boolean
	 */
	protected function postSave() {
		return true;
	}

	/**
	 * Deletes the document from the database.
	 * @return boolean True if preDelete, delete and postDelete completed successfully, otherwise false
	 * @throws Exception
	 */
	public function delete() {
		if (!$this->preDelete()) {
			return false;
		}

		$this->requireDatabase();
		$this->requireId();
		$this->requireRev();

		$this->options["rev"] = $this->document->_rev;

		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/" . rawurlencode($this->document->_id) . self::encodeOptions($this->options));
		$request->setMethod("delete");
		$request->send();

		$request->response = json_decode($request->response);

		if ($request->status === 200) {
			$this->document->_id = $request->response->id;
			$this->document->_rev = $request->response->rev;

			return $this->postDelete();
		} else {
			throw new Exception(self::describeError($request->response));
		}
	}

	/**
	 *  Is fired at the beginning of CouchDbDocument::delete() and determines
	 *  whether it continues or is aborted.
	 *  @return boolean
	 */
	protected function preDelete() {
		return true;
	}

	/**
	 * Is fired at the end of CouchDbDocument::delete() and determines its
	 * return value.
	 * @return boolean
	 */
	protected function postDelete() {
		return true;
	}

	/**
	 * Tells the database to include the document's meta field $fieldName in
	 * the response.
	 * @param string $fieldName Name of the meta field
	 * @return CouchDbDocument
	 */
	public function includeMetaField($fieldName) {
		$fieldName = ltrim($fieldName, "_");
		$this->options[$fieldName] = true;
		return $this;
	}

	/**
	 * Checks if _id is set.
	 * @throws Exception
	 */
	protected function requireId() {
		if (empty($this->document->_id)) {
			throw new Exception("CouchDB: Please provide an _id.");
		}
	}

	/**
	 * Checks if _rev is set.
	 * @throws Exception
	 */
	protected function requireRev() {
		if (empty($this->document->_rev)) {
			throw new Exception("CouchDB: Please provide a _rev.");
		}
	}

	/**
	 * Merges $thing recursively with the document. Fields in $thing supersede
	 * similarly named fields in the document.
	 * @param array|object $thing
	 */
	public function merge($thing) {
		$this->document = self::_merge($this->document, $thing);
	}

	/**
	 * Merges two things recursively. Fields in $thing2 supersede similarly
	 * named fields in $thing1.
	 * @param array|object $thing1
	 * @param array|object $thing2
	 */
	protected static function _merge($thing1, $thing2) {
		$thing1 = json_decode(json_encode($thing1));
		$thing2 = json_decode(json_encode($thing2));

		foreach ($thing2 as $fieldName => $fieldValue) {
			if (isset($thing1->$fieldName) && is_object($fieldValue)) {
				$thing1->$fieldName = self::_merge($thing1->$fieldName, $thing2->$fieldName);
			} else {
				$thing1->$fieldName = $fieldValue;
			}
		}

		return $thing1;
	}

	public function toArray() {
		return json_decode(json_encode($this->document), true);
	}

	/**
	 * Creates a CouchDbDocument object, fills it with data from $array and
	 * returns it.
	 * @param array $array
	 * @return CouchDbDocument
	 */
	public static function createFromArray($array) {
		if (!isset($array["_id"])) {
			throw new Exception("CouchDB: Array is missing field '_id'.");
		}

		$document = new CouchDbDocument($array["_id"]);

		foreach ($array as $fieldName => $fieldValue) {
			$document->$fieldName = $fieldValue;
		}

		return $document;
	}

	/**
	 * Creates a CouchDbDocument object, fills it with data from stdClass object
	 * and returns it.
	 * @param stdClass $object
	 * @return CouchDbDocument
	 */
	public static function createFromObject(stdClass $object) {
		if (!isset($object->_id)) {
			throw new Exception("CouchDB: Object is missing field '_id'.");
		}

		$document = new CouchDbDocument($object->_id);

		foreach ($object as $fieldName => $fieldValue) {
			$document->$fieldName = $fieldValue;
		}

		return $document;
	}
}
?>