<?php
namespace Models\CouchDb;

use \Exception as Exception;
use Libraries\HttpRequest as HttpRequest;

class CouchDbView extends CouchDb {
	/**
	 * Name of the CouchDB design.
	 * @var string
	 */
	protected $designName;

	/**
	 * Name of the CouchDB view that belongs to the design.
	 * @var string
	 */
	protected $viewName;

	/**
	 * Contains the actual view.
	 * @var stdClass
	 */
	protected $view;

	/**
	 * Contains the parameters passed in the URL when querying the view
	 * @var array
	 */
	protected $options = array();

	/**
	 * Contains keys the CouchDB view should use to filter the result set.
	 * @var array
	 */
	protected $keys = array();

	/**
	 * Names of the querying options that are currently supported by CouchDB (as
	 * of CouchDB 1.0.1).
	 * @var string
	 */
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

	/**
	 * Constructs the object.
	 * @param string $designName
	 * @param string $viewName
	 */
	public function __construct($designName = "", $viewName = "") {
		$this->designName = $designName;
		$this->viewName = $viewName;
		$this->view = new \stdClass();
	}

	/**
	 * Returns a view field (only if the read request comes from outside the
	 * class).
	 * @param string $fieldName
	 */
	public function __get($fieldName) {
		return isset($this->view->$fieldName) ? $this->view->$fieldName : null;
	}

	/**
	 * Checks if a view field is set (only if the isset request comes from
	 * outside the class).
	 * @param string $fieldName
	 * @return boolean
	 */
	public function __isset($fieldName) {
		return isset($this->view->$fieldName);
	}

	/**
	 * Returns the view in JSON format.
	 * @return string View in JSON format.
	 */
	public function __toString() {
		// Make a copy of the view
		$view = $this->view;

		// Convert any included documents from CouchDbDocument to stdClass so
		// the JSON encoder has access to the document fields from outside.
		if (isset($view->rows)) {
			foreach ($view->rows as &$item) {
				if (!isset($item->doc)) {
					break;
				}

				$item->doc = json_decode((string) $item->doc);
			}
		}

		return json_encode($view);
	}

	/**
	 * Fetches the view from the database.
	 * @return CouchDbView
	 * @throws Exception
	 */
	public function fetch() {
		$this->requireDatabase();

		if (empty($this->designName)) {
			throw new Exception("CouchDB: Please provide the design name.");
		}

		if (empty($this->viewName)) {
			throw new Exception("CouchDB: Please provide the view name.");
		}

		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/_design/" . rawurlencode($this->designName) . "/_view/" . rawurlencode($this->viewName) . self::encodeOptions($this->options));
		$request->setMethod(empty($this->keys) ? "get" : "post");
		$request->setData(json_encode(array("keys" => $this->keys)));
		$request->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$request->send();

		$request->response = json_decode($request->response);

		if ($request->status === 200) {
			return $this->createFromObject($request->response);
		} else {
			throw new Exception(self::describeError($request->response, $this->designName, $this->viewName));
		}
	}

	/**
	 * Fetches the specialized view "_all_docs" from the database.
	 * @return CouchDbView
	 * @throws Exception
	 */
	public function fetchAllDocs() {
		$this->requireDatabase();

		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/_all_docs" . self::encodeOptions($this->options));
		$request->setMethod(empty($this->keys) ? "get" : "post");
		$request->setData(json_encode(array("keys" => $this->keys)));
		$request->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$request->send();

		$request->response = json_decode($request->response);

		if ($request->status === 200) {
			return $this->createFromObject($request->response);
		} else {
			throw new Exception(self::describeError($request->response));
		}
	}

	/**
	 * Fills the current object with data from stdClass object.
	 * @param stdClass $object
	 * @return CouchDbView
	 */
	protected function createFromObject(\stdClass $object) {
		foreach ($object as $fieldName => $fieldValue) {
			$this->view->$fieldName = $fieldValue;
		}

		// Convert any included documents from array to object
		if (isset($this->view->rows)) {
			foreach ($this->view->rows as &$item) {
				if (!isset($item->doc)) {
					break;
				}

				$item->doc = CouchDbDocument::createFromObject($item->doc);
			}
		}

		return $this;
	}

	/**
	 * Sets querying options that are used for querying the CouchDB view.
	 * @param string $option Any querying option supported by the CouchDB view
	 * @param mixed $value
	 * @return CouchDbView
	 */
	public function setOption($option, $value) {
		$this->options[$option] = $value;
		return $this;
	}

	/**
	 * Tells the CouchDB view to only return items that match one of the keys.
	 * @param array $keys
	 * @return CouchDbView
	 */
	public function filterByKeys($keys) {
		$this->keys = $keys;
		return $this;
	}
}
?>