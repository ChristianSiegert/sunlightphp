<?php
class Model {
	private $controller;

	public $models = array();

	public $modelName;

	public $validate = array();
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

	public function getDocument($documentId, $method = "GET") {
		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId);
		list($status, $header, $data) = $this->query($url, $method);

		if ($status === 200) {
			return array($header, $data);
		} elseif ($status === 404) {
			throw new Exception("The document you try to fetch does not exist.");
		} else {
			throw new Exception("The document could not be fetched (Status $status).");
		}
	}

	public function documentExists($documentId) {
		try {
			$this->getDocument($documentId, "HEAD");
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

		// Add _id field
		$data["_id"] = $documentId;

		// Add type field
		if (!isset($data["type"])) {
			$data["type"] = lcfirst($this->modelName);
		}

		// Validate data
		$this->validationErrors = $this->validate($data);
		$this->controller->validationErrors = $this->validationErrors;

		if (empty($this->validationErrors)) {
			$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($data["_id"]);

			list($status, $header, $data) = $this->query($url, "PUT", json_encode($data));

			if ($status === 201) {
				return array($header, $data);
			} elseif ($status === 409) {
				throw new Exception("A document with this _id already exists.");
			} else {
				throw new Exception("Document could not be saved (Status $status).");
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

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/" . rawurlencode($documentId) . "?rev=" . rawurlencode($revision);
		return $this->query($url, "DELETE");
	}

	/**
	 * Gets the revision by reading a document's e-tag.
	 *
	 * @param string $documentId
	 * @return string Document revision ("_rev")
	 */
	public function getRevision($documentId) {
		list($header) = $this->getDocument($documentId, "HEAD");

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
		$parametersAsString = "?";

		foreach ($parameters as $parameterName => $parameterValue) {
			$parametersAsString .= $parameterName . "=" . rawurlencode(json_encode($parameterValue)) . "&";
		}

		if ($data !== null) {
			$data = json_encode($data);
		}

		$url = DATABASE_HOST . "/" . rawurlencode(DATABASE_NAME) . "/_design/" . rawurlencode($designName) . "/_view/" . rawurlencode($viewName) . $parametersAsString;
		list($status, $header, $data) = $this->query($url, $method, $data, $jsonDecodeResponse);

		if ($status === 200) {
			return array($header, $data);
		} elseif ($status === 400) {
			throw new Exception("CouchDB: Bad request: Invalid UTF-8 JSON.");
		} elseif ($status === 404) {
			throw new Exception("CouchDB: View '$viewName' does not exist for design '$designName'.");
		} else {
			throw new Exception("CouchDB: Could not get view (Status $status).");
		}
	}

	public function validate($data) {
		$validationErrors = array();

		foreach ($this->validate as $fieldName => $ruleSet) {
			if (!empty($ruleSet)) {
				foreach ($ruleSet as $ruleName => $rule) {
					if (!isset($data[$fieldName])) {
						$data[$fieldName] = "";
					}

					if (is_string($rule["rule"])) {
						$validates = $this->$rule["rule"]($data[$fieldName]);
					} elseif (is_array($rule["rule"])) {
						$validates = $this->$rule["rule"][0]($data[$fieldName], $rule["rule"]);
					}

					if (!$validates) {
						if (isset($rule["message"])) {
							$errorMessage = $rule["message"];
						} else {
							$errorMessage = $ruleName;
						}

						$validationErrors[$fieldName][] = $errorMessage;
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
		return preg_match('/^[0-9]+$/', $value);
	}

	public function isInRange($value, $arguments) {
		if ($this->isNumeric($value) && $value >= $arguments[1] && $value <= $arguments[2]) {
			return true;
		}

		return false;
	}

	public function isTimestamp($value) {
		if ($this->isNumeric($value) && $value >= 0 && $value <= time()) {
			return true;
		}

		return false;
	}
}
?>