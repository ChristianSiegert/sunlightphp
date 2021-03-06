<?php
class Model {
	private $controller;

	/**
	 * Additional models to be loaded through this model.
	 *
	 * @var array
	 */
	public $models = array();

	public $modelName;

	public $validationRules = array();
	public $validationErrors = array();

	public static $queryCount = 0;

	public function __construct(&$controller) {
		$this->controller = $controller;
		$this->modelName = get_class($this);
	}

	public function query($url, $method = "GET", $data = array(), $jsonDecodeResponse = true, $curlOptions = array()) {
		$handle = curl_init();

		$finalCurlOptions = array(
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_NOBODY => $method === "HEAD",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => USERAGENT
		);

		foreach ($curlOptions as $key => $value) {
			$finalCurlOptions[$key] = $value;
		}

		if ($method === "POST" || $method === "PUT") {
			$finalCurlOptions[CURLOPT_POSTFIELDS] = $data;
		}

		curl_setopt_array($handle, $finalCurlOptions);

		$rawResponse = curl_exec($handle);
		self::$queryCount++;

		if (curl_errno($handle)) {
			throw new Exception("Curl: " . curl_error($handle) . " (Curl error code: " . curl_errno($handle) . ").");
		}

		$info = curl_getinfo($handle);
		curl_close($handle);

		// Convert encoding if necessary
		if (preg_match('#charset=(.+)$#', $info["content_type"], $match)
				&& mb_convert_case($match[1], MB_CASE_UPPER) !== mb_internal_encoding()) {
			$rawResponse = mb_convert_encoding($rawResponse, mb_internal_encoding(), $match[1]);
		}

		$headers = array();

		// Matches header at the beginning of a string
		$pattern = '#^HTTP/1\..*(?=(?:\n|\r\n){2,})#sU';

		while (preg_match($pattern, $rawResponse, $rawHeader)) {
			$header = array();

			foreach (explode("\r\n", $rawHeader[0]) as $i => $line) {
				if ($i === 0) {
					$header["Status"] = $line;
				} else {
					$explodedLine = explode(": ", $line, 2);
					$header[$explodedLine[0]] = isset($explodedLine[1]) ? $explodedLine[1] : "";
				}
			}

			$headers[] = $header;

			// Remove header from $rawResponse
			$rawResponse = trim(preg_replace($pattern, "", $rawResponse, 1));
		}

		// $rawResponse now contains only data as we have removed all headers
		$response = trim($rawResponse);

		if ($jsonDecodeResponse) {
			$response = json_decode($response, true);
		}

		return array($info["http_code"], $headers, $response, $info);
	}

	/**
	 * Retrieves a document from CouchDB.
	 *
	 * Throws exception on error or if document does not exist. Accepts any
	 * parameters CouchDB supports.
	 *
	 * @param string $documentId The id of the document to retrieve
	 * @param string $revision The revision of the document to retrieve
	 * @param array $parameters Parameters used when querying CouchDB
	 * @param string $method HTTP method used when querying CouchDB
	 * @return array Array containing HTTP response header and document
	 */
	public function getDocument($documentId, $revision = "", $parameters = array()) {
		if (!empty($revision)) {
			$parameters["rev"] = $revision;
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId) . $this->encodeParameters($parameters);
		list($status, $headers, $response) = $this->query($url);

		if ($status === 200) {
			return $response;
		} else {
			throw new Exception($this->describeError($response, $documentId, $revision));
		}
	}

	public function deleteDocument($documentId, $revision = "") {
		$parameters = array(
			"rev" => empty($revision) ? $this->getRevision($documentId) : $revision
		);

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId) . $this->encodeParameters($parameters);
		list($status, $headers, $response) = $this->query($url, "DELETE");

		if ($status === 200) {
			return $response;
		} else {
			throw new Exception($this->describeError($response));
		}
	}

	/**
	 * Returns true if all fields in the document are whitelisted, otherwise it
	 * returns the name of the first field that is not whitelisted.
	 *
	 * @param array $document
	 * @param array $fieldList
	 * @return mixed true if all fields are whitelisted, otherwise string with name of first non-whitelisted field
	 */
	public static function onlyContainsWhitelistedFields($document, $fieldList) {
		foreach ($document as $fieldName => $fieldValue) {
			if (is_array($fieldValue)) {
				if (isset($fieldList[$fieldName]) && ($fieldName = self::onlyContainsWhitelistedFields($fieldValue, $fieldList[$fieldName])) === true) {
					continue;
				}
			} elseif (in_array($fieldName, $fieldList)) {
				continue;
			}

			return $fieldName;
		}

		return true;
	}

	public function storeDocument($documentId, $document, $options = array()) {
		if (empty($options["fieldList"])) {
			throw new Exception("Please whitelist fields. Aborted storing document.");
		}

		/* TODO: Fix onlyContainsWhitelistedFields() (see model.test.php)
		if (!self::onlyContainsWhitelistedFields($document, $options["fieldList"])) {
			throw new Exception("Non-whitelisted field '$fieldName' is present. Aborted storing document.");
		}
		*/

		// Abort if non-whitelisted fields are present
		foreach ($document as $fieldName => $value) {
			if (!in_array($fieldName, $options["fieldList"])) {
				throw new Exception("Non-whitelisted field '$fieldName' is present. Aborted storing document.");
			}
		}

		// Add _id field so it can be validated
		$document["_id"] = $documentId;

		// Add type field
		if (!isset($document["type"])) {
			$document["type"] = lcfirst($this->modelName);
		}

		// Validate data if rules are defined for this document type
		if (isset($this->validationRules[$document["type"]])) {
			$this->validationErrors = $this->validate($document, $this->validationRules[$document["type"]]);
			$this->controller->validationErrors = $this->validationErrors;
		} else {
			throw new Exception("Please define validation rules for document type '{$document["type"]}'. Aborted storing documents.");
		}

		if (empty($this->validationErrors)) {
			$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId);
			list($status, $headers, $response) = $this->query($url, "PUT", json_encode($document));

			if ($status === 201) {
				return $response;
			} else {
				throw new Exception($this->describeError($response));
			}
		} else {
			throw new Exception("Data is not valid. Aborted storing document.");
		}
	}

	public function updateDocument($documentId, $revision = "", $document, $options = array()) {
		if (!isset($document["_rev"])) {
			$document["_rev"] = empty($revision) ? $this->getRevision($documentId) : $revision;
			$options["fieldList"][] = "_rev";
		}

		return $this->storeDocument($documentId, $document, $options);
	}

	/**
	 * Gets the revision by reading a document's e-tag.
	 *
	 * @param string $documentId
	 * @return string Document revision ("_rev")
	 */
	public function getRevision($documentId) {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId);
		list($status, $headers) = $this->query($url, "HEAD");

		$mostRecentHeader = count($headers) - 1;

		if ($status === 200
				&& isset($headers[$mostRecentHeader]["Etag"])
				&& preg_match('/^"([^"]+)"$/', $headers[$mostRecentHeader]["Etag"], $eTag)) {
			return $eTag[1];
		} else {
			throw new Exception("CouchDB: Getting the document's latest revision failed.");
		}
	}

	/**
	 * Checks if a document exists.
	 *
	 * @param string $documentId
	 * @param string $revision
	 * @return bool True if the document exists, otherwise false
	 */
	public function documentExists($documentId, $revision = "") {
		$parameters = !empty($revision) ? array("rev" => $revision) : array();

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId) . $this->encodeParameters($parameters);
		list($status, $headers, $response) = $this->query($url);

		if ($status === 200) {
			return true;
		} elseif ($status === 404
				&& is_array($response)
				&& isset($response["error"])
				&& $response["error"] === "not_found"
				&& isset($response["reason"])
				&& $response["reason"] === "missing") {
			return false;
		} else {
			throw new Exception($this->describeError($response, $documentId, $revision));
		}
	}

	public function getDocuments($documentIds = array(), $parameters = array()) {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_all_docs" . $this->encodeParameters($parameters);
		list($status, $headers, $response) = $this->query($url, empty($documentIds) ? "GET" : "POST", json_encode(array("keys" => $documentIds)));

		if ($status === 200 && isset($response["rows"])) {
			return $response["rows"];
		} else {
			throw new Exception($this->describeError($response));
		}
	}

	public function storeDocuments($documents, $options) {
		if (empty($options["fieldList"])) {
			throw new Exception("Please whitelist fields. Aborted storing documents.");
		}

		$documentCount = count($documents);
		for ($i = 0; $i < $documentCount; $i++) {
			// Abort if no id is provided
			if (!isset($documents[$i]["_id"])) {
				throw new Exception("Please provide an id. Aborted storing documents.");
			}

			// Abort if non-whitelisted fields are present
			foreach ($documents[$i] as $fieldName => $value) {
				if (!in_array($fieldName, $options["fieldList"])) {
					throw new Exception("Non-whitelisted field '$fieldName' is present. Aborted storing documents.");
				}
			}

			// Add type field
			if (!isset($documents[$i]["type"])) {
				$documents[$i]["type"] = lcfirst($this->modelName);
			}

			// Validate data if rules are defined for this document type
			if (isset($this->validationRules[$documents[$i]["type"]])) {
				$this->validationErrors = $this->validate($documents[$i], $this->validationRules[$documents[$i]["type"]]);
				$this->controller->validationErrors = $this->validationErrors;
			} else {
				throw new Exception("Please define validation rules for document type '{$documents[$i]["type"]}'. Aborted storing documents.");
			}
		}

		if (empty($this->validationErrors)) {
			$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_bulk_docs";
			list($status, $headers, $response) = $this->query($url, "POST", json_encode(array("docs" => $documents)), true, array(CURLOPT_HTTPHEADER => array("Content-Type: application/json")));

			if ($status === 201) {
				return $response;
			} else {
				throw new Exception($this->describeError($response));
			}
		} else {
			throw new Exception("Data is not valid. Aborted storing documents. \n" . express($this->validationErrors));
		}
	}

	public function deleteDocuments($documents) {
		foreach ($documents as $document) {
			if (!isset($document["_id"])) {
				throw new Exception("Missing document id. Aborted deleting documents.");
			}

			if (!isset($document["_rev"])) {
				throw new Exception("Missing document rev. Aborted deleting documents.");
			}

			if (!isset($document["_deleted"])) {
				throw new Exception("Missing 'deleted' field. Aborted deleting documents.");
			}
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_bulk_docs";
		list($status, $headers, $response) = $this->query($url, "POST", json_encode(array("docs" => $documents)), true, array(CURLOPT_HTTPHEADER => array("Content-Type: application/json")));

		if ($status === 201) {
			return $response;
		} else {
			throw new Exception($this->describeError($response));
		}
	}

	public function getView($designName, $viewName, $parameters = array(), $data = array()) {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_design/" . rawurlencode($designName) . "/_view/" . rawurlencode($viewName) . $this->encodeParameters($parameters);
		list($status, $headers, $response) = $this->query($url, empty($data) ? "GET" : "POST", json_encode($data), true, array(CURLOPT_HTTPHEADER => array("Content-Type: application/json")));

		if ($status === 200 && isset($response["rows"])) {
			return $response["rows"];
		} else {
			throw new Exception($this->describeError($response, $designName, $viewName));
		}
	}

	public function getList($designName, $listName, $viewName, $parameters = array(), $data = array()) {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_design/" . rawurlencode($designName) . "/_list/" . rawurlencode($listName) . "/" . rawurlencode($viewName) . $this->encodeParameters($parameters);
		list($status, $headers, $response) = $this->query($url, empty($data) ? "GET" : "POST", json_encode($data), false);

		if ($status === 200) {
			return $response;
		} else {
			throw new Exception($this->describeError(json_decode($response, true)));
		}
	}

	public function encodeParameters($parameters) {
		$parametersAsString = "?";

		foreach ($parameters as $parameterName => $parameterValue) {
			if ($parameterName === "rev"
					|| $parameterName === "startkey_docid"
					|| $parameterName === "endkey_docid"
					|| $parameterName === "stale") {
				$parametersAsString .= $parameterName . "=" . rawurlencode($parameterValue) . "&";
			} else {
				$parametersAsString .= $parameterName . "=" . rawurlencode(json_encode($parameterValue)) . "&";
			}
		}

		return $parametersAsString;
	}

	public function describeError($response) {
		if (isset($response["error"]) && isset($response["reason"])) {
			$arguments = func_get_args();

			switch ($response["error"]) {
				case "conflict":
					switch ($response["reason"]) {
						case "Document update conflict.":
							return "CouchDB: The document could not be updated/deleted.";
						default: break;
					}
				case "not_found":
					switch ($response["reason"]) {
						case "missing":
							return empty($arguments[2]) ? "CouchDB: Document '{$arguments[1]}' does not exist." : "CouchDB: Document '{$arguments[1]}' with revision '{$arguments[2]}' does not exist.";
						case "missing_named_view":
							return "CouchDB: View '{$arguments[2]}' does not exist in design '{$arguments[1]}'.";
						case "no_db_file":
							return "CouchDB: Database '" . DATABASE_NAME . "' does not exist.";
						default: break;
					}
			}
		}

		return express($response);
	}

	public function validate($document, $rules) {
		$validationErrors = array();

		foreach ($rules as $fieldName => $rule) {
			if (isset($rule["rule"])) {
				if (!array_key_exists($fieldName, $document)) {
					$document[$fieldName] = "";
				}

				// The name of the function used to validate the field is passed as string
				if (is_string($rule["rule"])) {
					$validates = $this->{$rule["rule"]}($document[$fieldName]);
				// The name of the function used to validate the field is passed in an array (as first argument)
				} elseif (is_array($rule["rule"])) {
					$arguments = $rule["rule"];
					$arguments[0] = $document[$fieldName];

					$validates = call_user_func_array(array($this, $rule["rule"][0]), $arguments);
				} else {
					throw new UnexpectedValueException("Rule for field '$fieldName' must either be a string or array (" . gettype($rule["rule"]) . " given).");
				}

				if (!$validates) {
					$validationErrors[$fieldName][] = array(
						"message" => !empty($rule["message"]) ? $rule["message"] : "Value for field '$fieldName' is not valid.",
						"value" => $document[$fieldName],
					);
				}
			} elseif (isset($rule["contains"])) {
				$errors = $this->validate($document[$fieldName], $rule["contains"]);

				if (!empty($errors)) {
					$validationErrors[$fieldName] = $errors;
				}
			} else {
				throw new InvalidArgumentException("Validation rule for field '$fieldName' is not formed properly. Please add 'rule' or 'contains'.");
			}
		}

		return $validationErrors;
	}

	public function isBoolean($value) {
		return is_bool($value);
	}

	public function isInRange($value, $min, $max, $strict = true) {
		return $this->isNumeric($value, $strict) && $value >= $min && $value <= $max;
	}

	public function isNotEmpty($field) {
		return !empty($field);
	}

	public function isNumeric($value, $strict = true) {
		return (is_integer($value) || (!$strict && is_string($value))) && preg_match('/^[0-9]+$/', $value);
	}

	public function isSha1Hash($value) {
		return preg_match('#^[0-9a-z]{40}$#', $value);
	}

	public function isTimestamp($value, $strict = true) {
		return $this->isInRange($value, 0, time(), $strict);
	}

	public function isUrl($value) {
		return preg_match('#^https?://#', $value);
	}
}
?>