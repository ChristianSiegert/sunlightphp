<?php
namespace Models;

use \InvalidArgumentException;

class Document extends CouchDb\CouchDbDocument {
	/**
	 * Contains the validation rules that are used to validate the document
	 * content.
	 * @var array
	 */
	protected $validationRules = array();

	/**
	 * Contains validation errors.
	 * @var array
	 */
	protected $validationErrors = array();

	/**
	 * Contains a list of fields that are allowed to be present when merging the
	 * document. Fields that are in the document but not in this list are
	 * regarded as hostile. Saving the document will then be prohibited.
	 * @var array
	 */
	protected $whitelist = array();

	/**
	 * Constructs the Document object and sets the database. You can overwrite
	 * the default database by calling $this->setDatabase() manually after you
	 * created the Document.
	 */
	public function __construct() {
		parent::__construct();
		$this->setDatabase(DATABASE_HOST, DATABASE_NAME);
	}

	/**
	 * Validates the document and, if it is valid, saves it.
	 * @throws \Models\DocumentException
	 * @see CouchDbDocument::save()
	 */
	public function save() {
		if (empty($this->document->type)) {
			$this->document->type = get_class($this);
		}

		if (empty($this->validationRules)) {
			throw new DocumentException("Please define validation rules for document type '{$this->document->type}'.", DocumentException::MISSING_VALIDATION_RULES);
		}

		$this->validationErrors = $this->validate($this, $this->validationRules);

		if ($this->validationErrors) {
			throw new DocumentException("Data did not validate successfully.", DocumentException::HAS_VALIDATION_ERRORS);
		}

		return parent::save();
	}

	/**
	 * Sets the merge whitelist.
	 * @param array $whitelist
	 */
	public function setWhitelist($whitelist) {
		$this->whitelist = $whitelist;
	}

	/**
	 * Merges $thing recursively with the document. Fields in $thing supersede
	 * similarly named fields in the document. The fields in $thing must be
	 * whitelisted.
	 * @param array|object $thing
	 * @throws \Models\DocumentException
	 * @see \Models\CouchDb\CouchDbDocument::merge()
	 */
	public function merge($thing) {
		if (empty($this->whitelist)) {
			throw new DocumentException("Please set a whitelist before the merge.", DocumentException::MISSING_WHITELIST);
		}

		$thing = json_decode(json_encode($thing));

		$result = self::checkAgainstWhitelist($thing, $this->whitelist);

		if ($result !== true) {
			throw new DocumentException("Data contains non-whitelisted field '$result'", DocumentException::NON_WHITELISTED_FIELD_PRESENT);
		}

		parent::merge($thing);
	}

	/**
	 * Returns true if all fields in the document are whitelisted, otherwise it
	 * returns the name of the first field that is not whitelisted.
	 *
	 * @param mixed $thing
	 * @param array $whitelist
	 * @return mixed
	 */
	protected static function checkAgainstWhitelist($document, $whitelist) {
		$document = json_decode(json_encode($document));

		foreach ($document as $fieldName => $fieldValue) {
			if (is_object($fieldValue)) {
				if (isset($whitelist[$fieldName]) && ($fieldName = self::checkAgainstWhitelist($fieldValue, $whitelist[$fieldName])) === true) {
					continue;
				}
			} elseif (in_array($fieldName, $whitelist)) {
				continue;
			}

			return $fieldName;
		}

		return true;
	}

	/**
	 * Validates an object/array based on the provided validation rules.
	 * @param object|array $document
	 * @param array $rules Validation rules
	 * @return array Validation errors
	 * @throws \InvalidArgumentException
	 */
	public function validate($document, $rules) {
		$validationErrors = array();

		foreach ($rules as $fieldName => $rule) {
			if (is_object($document) && !isset($document->$fieldName)) {
				$document->$fieldName = "";
			} elseif (is_array($document) && !array_key_exists($fieldName, $document)) {
				$document[$fieldName] = "";
			}

			if (is_object($document)) {
				$fieldValue = $document->$fieldName;
			} else if (is_array($document)) {
				$fieldValue = $document[$fieldName];
			} else {
				$fieldValue = $document;
			}

			if (isset($rule["rule"])) {
				// The name of the function used to validate the field is passed as string
				if (is_string($rule["rule"])) {
					if (method_exists($this, $rule["rule"])) {
						$validates = $this->$rule["rule"]($fieldValue);
					} elseif (function_exists($rule["rule"])) {
						$validates = call_user_func($rule["rule"], $fieldValue);
					} else {
						throw new InvalidArgumentException("Rule '{$rule["rule"]}' is not referencing an existing function or method.");
					}
				// The name of the function used to validate the field is passed in an array (as first argument)
				} elseif (is_array($rule["rule"])) {
					$arguments = $rule["rule"];
					$arguments[0] = $fieldValue;

					$validates = call_user_func_array(array($this, $rule["rule"][0]), $arguments);
				} else {
					throw new InvalidArgumentException("Rule for field '$fieldName' must either be a string or array (" . gettype($rule["rule"]) . " given).");
				}

				if (!$validates) {
					$validationErrors[$fieldName][] = array(
						"message" => !empty($rule["message"]) ? $rule["message"] : "Value for field '$fieldName' is not valid.",
						"value" => $fieldValue,
					);
				}
			} elseif (isset($rule["contains"])) {
				$errors = $this->validate($fieldValue, $rule["contains"]);

				if (!empty($errors)) {
					$validationErrors[$fieldName] = $errors;
				}
			} else {
				throw new InvalidArgumentException("Validation rule for field '$fieldName' is not formed properly. Please add 'rule' or 'contains'.");
			}
		}

		return $validationErrors;
	}

	/**
	 * Returns validation errors.
	 * @return array
	 */
	public function getValidationErrors() {
		return $this->validationErrors;
	}

	public static function isInRange($value, $min, $max, $strict = true) {
		return self::isNumeric($value, $strict) && $value >= $min && $value <= $max;
	}

	public static function isNotEmpty($field) {
		return !empty($field);
	}

	public static function isNumeric($value, $strict = true) {
		return (is_integer($value) || (!$strict && is_string($value))) && preg_match('/^[0-9]+$/', $value);
	}

	public static function isSha1Hash($value) {
		return preg_match('#^[0-9a-f]{40}$#', $value);
	}

	public static function isTimestamp($value, $strict = true) {
		return self::isInRange($value, 0, time(), $strict);
	}

	public static function isUrl($value) {
		return preg_match('#^https?://#', $value);
	}
}
?>