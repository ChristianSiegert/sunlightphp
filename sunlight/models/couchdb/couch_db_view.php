<?php
class CouchDbView extends CouchDb {
	protected $designName;
	protected $viewName;

	protected $options = array();
	protected $data = array();

	const OPTION_DESCENDING = "descending";
	const OPTION_ENDKEY = "endkey";
	const OPTION_ENDKEY_DOCID = "endkey_docid";
	const OPTION_GROUP = "group";
	const OPTION_GROUP_LEVEL = "group_level";
	const OPTION_INCLUDE_DOCS = "include_docs";
	const OPTION_INCLUSIVE_END = "inclusive_end";
	const OPTION_KEY = "key";
	const OPTION_LIMIT = "limit";
	const OPTION_REDUCE = "reduce";
	const OPTION_SKIP = "skip";
	const OPTION_STALE = "stale";
	const OPTION_STARTKEY = "startkey";
	const OPTION_STARTKEY_DOCID = "startkey_docid";

	public function __construct($designName = "", $viewName = "") {
		$this->designName = $designName;
		$this->viewName = $viewName;
	}

	public function fetch() {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_design/" . rawurlencode($this->designName) . "/_view/" . rawurlencode($this->viewName) . self::encodeOptions($this->options);
		list($status, $headers, $response) = HttpRequest::query($url, empty($this->data) ? "GET" : "POST", json_encode($this->data), array(CURLOPT_HTTPHEADER => array("Content-Type: application/json")));

		$response = json_decode($response);

		if ($status === 200) {
			return $this->createFromResponse($response);
		} else {
			throw new Exception(self::describeError($response, $designName, $viewName));
		}
	}

	public function fetchAllDocs($documentIds = array()) {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_all_docs" . self::encodeOptions($this->options);
		list($status, $headers, $response) = HttpRequest::query($url, empty($documentIds) ? "GET" : "POST", json_encode(array("keys" => $documentIds)));

		$response = json_decode($response);

		if ($status === 200) {
			return $this->createFromResponse($response);
		} else {
			throw new Exception(self::describeError($response));
		}
	}

	public function setOption($option, $value) {
		$this->options[$option] = $value;
	}

	public function cleanUp() {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_view_cleanup";
		list($status, $headers, $response) = HttpRequest::query($url, "POST", array(), array(CURLOPT_HTTPHEADER => array("Content-Type: application/json")));

		$response = json_decode($response);

		if ($status === 202) {
			return $this->createFromResponse($response);
		} else {
			throw new Exception(self::describeError($response));
		}
	}

	protected function createFromResponse($response) {
		foreach ($response as $fieldName => $fieldValue) {
			$this->$fieldName = $fieldValue;
		}

		// Convert any included documents from array to object
		if (isset($this->rows)) {
			foreach ($this->rows as &$item) {
				if (!isset($item->doc)) {
					break;
				}

				$item->doc = CouchDbDocument::createFromArray($item->doc);
			}
		}

		return $this;
	}
}
?>