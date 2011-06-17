<?php
namespace Models\CouchDb;

use Models\DocumentException;

class CouchDb {
	protected $databaseHost = "";
	protected $databaseName = "";

	public function setDatabase($databaseHost, $databaseName) {
		$this->databaseHost = $databaseHost;
		$this->databaseName = $databaseName;
	}

	/**
	 * Checks if the database host and database name is set.
	 * @throws \Models\DocumentException
	 */
	protected function requireDatabase() {
		if (empty($this->databaseHost)) {
			throw new DocumentException("Please provide the database host.");
		}

		if (empty($this->databaseName)) {
			throw new DocumentException("Please provide the database name.");
		}
	}

	protected static function encodeOptions($options) {
		$encodedOptions = "?";

		foreach ($options as $optionName => $optionValue) {
			if ($optionName === "endkey_docid"
					|| $optionName === "rev"
					|| $optionName === "stale"
					|| $optionName === "startkey_docid") {
				$encodedOptions .= $optionName . "=" . rawurlencode($optionValue) . "&";
			} else {
				$encodedOptions .= $optionName . "=" . rawurlencode(json_encode($optionValue)) . "&";
			}
		}

		return $encodedOptions;
	}

	protected static function describeError($response) {
		if (isset($response->error) && isset($response->reason)) {
			$arguments = func_get_args();

			switch ($response->error) {
				case "conflict":
					switch ($response->reason) {
						case "Document update conflict.":
							return "The document could not be updated/deleted due to a conflict.";
						default: break;
					}
				case "not_found":
					switch ($response->reason) {
						case "deleted":
							return "Document '{$arguments[1]}' does not exist anymore.";
						case "missing":
							return empty($arguments[2]) ? "CouchDB: Document '{$arguments[1]}' does not exist." : "CouchDB: Document '{$arguments[1]}' with revision '{$arguments[2]}' does not exist.";
						case "missing_named_view":
							return "View '{$arguments[2]}' does not exist in design '{$arguments[1]}'.";
						case "no_db_file":
							return "Database '" . DATABASE_NAME . "' does not exist.";
						default: break;
					}
			}
		}

		return express($response);
	}
}
?>