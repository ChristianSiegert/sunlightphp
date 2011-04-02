<?php
class CouchDbDocument extends CouchDb {
	const OPTION_ATTACHMENTS = "attachments";
	const OPTION_CONFLICTS = "conflicts";
	const OPTION_DELETED = "deleted";
	const OPTION_DELETED_CONFLICTS = "deleted_conflicts";
	const OPTION_REVISIONS = "revisions";
	const OPTION_REVS_INFO = "revs_info";

	public function __construct($id = null, $revision = null, $options = array()) {
		if (empty($id)) {
			$this->type = strtolower(get_class($this));
		} else {
			$this->_id = $id;
			$this->_rev = $revision;
			$this->fetch($options);
		}
	}

	public function fetch($options = array()) {
		if (!empty($this->_rev)) {
			$options["rev"] = $this->_rev;
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($this->_id) . self::encodeOptions($options);
		list($status, $headers, $response) = HttpRequest::query($url);

		$response = json_decode($response);

		if ($status === 200) {
			foreach ($response as $fieldName => $fieldValue) {
				$this->$fieldName = $fieldValue;
			}
		} else {
			throw new Exception(self::describeError($response, $this->_id, $this->_rev));
		}
	}

	public function save() {
		if (empty($this->_id)) {
			throw new Exception("CouchDB: Please provide an _id. Cannot save document without it.");
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($this->_id);
		list($status, $headers, $response) = HttpRequest::query($url, "PUT", json_encode($this));

		$response = json_decode($response);

		if ($status === 201) {
			$this->_id = $response->id;
			$this->_rev = $response->rev;
		} else {
			throw new Exception(self::describeError($response));
		}
	}

	public function delete() {
		if (empty($this->_id)) {
			throw new Exception("CouchDB: Please provide the _id of the document you want to delete.");
		} elseif (empty($this->_rev)) {
			throw new Exception("CouchDB: Please provide the _rev of the document you want to delete.");
		}

		$options = array(
			"rev" => $this->_rev
		);

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($this->_id) . self::encodeOptions($options);
		list($status, $headers, $response) = HttpRequest::query($url, "DELETE");

		$response = json_decode($response);

		if ($status === 200) {
			$this->_id = $response->id;
			$this->_rev = $response->rev;
		} else {
			throw new Exception(self::describeError($response));
		}
	}

	public function setOption($option, $value) {
		$this->options[$option] = $value;
	}

	public static function createFromArray($array) {
		$document = new CouchDbDocument();

		foreach ($array as $fieldName => $fieldValue) {
			$document->$fieldName = $fieldValue;
		}

		return $document;
	}



	public function fieldExists($name) {

	}

	public function whitelist() {

	}

	public function validates($validationRules) {

	}

	public function toArray() {

	}

	public function update() {

	}

	public function replace() {

	}
}
?>