<?php
class Model {
	private $controller;

	public $models = array();

	public $modelName;

	public $validationRules = array();
	public $validationErrors = array();

	public function __construct(&$controller) {
		$this->controller = $controller;
		$this->modelName = ucfirst(Inflector::singularize($controller->params["controller"]));
	}

	public function query($url, $method = "GET", $data = null, $jsonDecodeResponse = true) {
		$handle = curl_init();

		curl_setopt_array($handle, array(
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => $method === "HEAD",
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $url
		));

		$rawResponse = curl_exec($handle);
		curl_close($handle);

		// Split response text at empty lines to separate headers and data
		$response = explode("\r\n\r\n", $rawResponse);

		// Get most recent header from all received headers
		$rawHeader = explode("\r\n", $response[count($response) - 2]);

		// Get status code and header fields
		$header = array();
		foreach ($rawHeader as $i => $line) {
			if ($i === 0) {
				 $rawStatus = explode(" ", $line);
				 $status = (integer) $rawStatus[1];
				 $header["Status"] = $line;
			} else {
				list($fieldName, $fieldValue) = explode(": ", $line, 2);
				$header[$fieldName] = $fieldValue;
			}
		}

		// Get data
		$data = $response[count($response) - 1];

		if ($jsonDecodeResponse) {
			$data = json_decode($data, true);
		}

		return array($status, $header, $data);
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
	public function getDocument($documentId, $revision = "", $parameters = array(), $method = "GET") {
		if (!empty($revision)) {
			$parameters["rev"] = $revision;
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId) . $this->encodeParameters($parameters);
		list($status, $header, $data) = $this->query($url, $method);

		if ($status === 200) {
			return array($header, $data);
		} elseif ($status === 404) {
			throw new Exception("The document you try to fetch does not exist.");
		} else {
			debug($header, $data);
			throw new Exception("The document could not be fetched (Status $status).");
		}
	}

	/**
	 * Checks if document exists.
	 *
	 * @param string $documentId
	 * @return bool
	 */
	public function documentExists($documentId) {
		try {
			$this->getDocument($documentId, "", array(), "HEAD");
			return true;
		} catch (Exception $exception) {
			return false;
		}
	}

	public function storeDocument($documentId, $data, $options) {
		if (empty($options["fieldList"])) {
			throw new Exception("Please whitelist fields. Aborted storing document.");
		}

		// Abort if non-whitelisted fields are present
		foreach ($data as $fieldName => $value) {
			if (!in_array($fieldName, $options["fieldList"])) {
				throw new Exception("Non-whitelisted field '$fieldName' is present. Aborted storing document.");
			}
		}

		// Add type field
		if (!isset($data["type"])) {
			$data["type"] = lcfirst($this->modelName);
		}

		// Validate data if rules are defined for this document type
		if (isset($this->validationRules[$data["type"]])) {
			$this->validationErrors = $this->validate($data, $this->validationRules[$data["type"]]);
			$this->controller->validationErrors = $this->validationErrors;
		} else {
			throw new Exception("Please define validation rules. Not validating data is a security risk. Aborted storing document.");
		}

		if (empty($this->validationErrors)) {
			$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId);
			list($status, $header, $data) = $this->query($url, "PUT", json_encode($data));

			if ($status === 201) {
				return array($header, $data);
			} elseif ($status === 409) {
				throw new Exception("A document with this id already exists.");
			} else {
				throw new Exception("Document could not be stored (Status $status).");
			}
		} else {
			throw new Exception("Data is not valid. Aborted storing document.");
		}
	}

	public function updateDocument($documentId, $data, $options = array()) {
		$data["_rev"] = $this->getRevision($documentId);
		$options["fieldList"][] = "_rev";

		return $this->storeDocument($documentId, $data, $options);
	}

	public function deleteDocument($documentId, $revision = null) {
		if ($revision === null) {
			$revision = $this->getRevision($documentId);
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId) . $this->encodeParameters(array("rev" => $revision));
		list($status, $header, $data) = $this->query($url, "DELETE");

		if ($status === 200) {
			return array($header, $data);
		} else {
			throw new Exception("Document could not be deleted (Status $status).");
		}
	}

	/**
	 * Gets the revision by reading a document's e-tag.
	 *
	 * @param string $documentId
	 * @return string Document revision ("_rev")
	 */
	public function getRevision($documentId) {
		list($header) = $this->getDocument($documentId, "", array(), "HEAD");

		if (isset($header["Etag"])) {
			if (preg_match('/^"([^"]+)"$/', $header["Etag"], $eTag) === 1) {
				return $eTag[1];
			} else {
				throw new Exception("Could not parse e-tag from response header. Getting the latest revision failed.");
			}
		} else {
			throw new Exception("E-tag is not present in response header. Getting the latest revision failed.");
		}
	}

	public function getView($designName, $viewName, $parameters = array(), $method = "GET", $data = null, $jsonDecodeResponse = true) {
		if ($data !== null) {
			$data = json_encode($data);
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_design/" . rawurlencode($designName) . "/_view/" . rawurlencode($viewName) . $this->encodeParameters($parameters);
		list($status, $header, $data) = $this->query($url, $method, $data, $jsonDecodeResponse);

		if ($status === 200) {
			return array($header, $data);
		} elseif ($status === 400) {
			if (isset($data["error"]) && isset($data["reason"])) {
				throw new Exception("CouchDB: {$data["error"]} &hellip; {$data["reason"]}.");
			} else {
				throw new Exception("CouchDB: Something is wrong with the request.<pre>" . print_r($data, true) . "</pre>");
			}
		} elseif ($status === 404) {
			throw new Exception("CouchDB: View '$viewName' does not exist for design '$designName'.");
		} else {
			throw new Exception("CouchDB: Could not get view (Status $status).");
		}
	}

	public function encodeParameters($parameters) {
		$parametersAsString = "?";

		foreach ($parameters as $parameterName => $parameterValue) {
			if ($parameterName === "rev") {
				$parametersAsString .= $parameterName . "=" . rawurlencode($parameterValue) . "&";
			} else {
				$parametersAsString .= $parameterName . "=" . rawurlencode(json_encode($parameterValue)) . "&";
			}
		}

		return $parametersAsString;
	}

	public function validate($document, $rules) {
		$validationErrors = array();

		foreach ($rules as $fieldName => $rule) {
			if (!empty($rule)) {
				if (!isset($document[$fieldName])) {
					$document[$fieldName] = null;
				}

				if (is_string($rule)) {
					$validates = $this->$rule($document[$fieldName]);

					if (!$validates) {
						$validationErrors[$fieldName] = "Invalid $fieldName.";
					}
				} elseif (is_array($rule)) {
					if (isset($rule[0])) {
						$function = array($this, $rule[0]);

						$parameters = $rule;
						$parameters[0] = $document[$fieldName];

						$validates = call_user_func_array($function, $parameters);

						if (!$validates) {
							$validationErrors[$fieldName] = "Invalid $fieldName.";
						}
					} else {
						$errors = $this->validate($document[$fieldName], $rules[$fieldName]);

						if (!empty($errors)) {
							$validationErrors[$fieldName] = $errors;
						}
					}
				}
			}
		}

		return $validationErrors;
	}

	public function isNotEmpty($field) {
		return !empty($field);
	}

	public function isNumeric($value) {
		return (is_string($value) || is_integer($value)) && preg_match('/^[0-9]+$/', $value);
	}

	public function isInRange($value, $min, $max) {
		return $this->isNumeric($value) && $value >= $min && $value <= $max;
	}

	public function isTimestamp($value) {
		return $this->isNumeric($value) && $value >= 0 && $value <= time();
	}

	public function isUrl($value) {
		return is_string($value) && preg_match('#^https?://#', $value);
	}
}
?>