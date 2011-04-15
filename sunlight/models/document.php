<?php
class Document extends CouchDbDocument {
	/**
	 * Contains the validation rules that are used to validate the content of
	 * the document.
	 * @var array
	 */
	protected $validationRules = array();

	/**
	 * Contains a list of fields that are allowed to be present when saving the
	 * document. Fields that are in the document but not in this list are
	 * regarded as hostile. Saving the document will then be prohibited.
	 * @var array
	 */
	protected $whitelist = array();

	/**
	 * The controller of the document. Can be any object, usually it is an
	 * instance of "Controller" or "Shell".
	 * @var object
	 */
	protected $controller;

	/**
	 * Exception codes.
	 * @var integer
	 */
	const EXCEPTION_INVALID_DATA = 1;
	const EXCEPTION_MISSING_WHITELIST = 2;
	const EXCEPTION_MISSING_VALIDATION_RULES = 3;
	const EXCEPTION_NON_WHITELISTED_FIELD_PRESENT = 4;

	/**
	 * Constructs the Document object and sets the database.
	 * @param object $controller
	 * @param string $id Document _id
	 * @param string $revision Document _rev
	 */
	public function __construct(&$controller, $id, $revision = "") {
		parent::__construct($id, $revision);
		$this->setDatabase(DATABASE_HOST, DATABASE_NAME);
		$this->controller = $controller;
	}

	/**
	 * Validates the document and, if it is valid, saves it.
	 * @see CouchDbDocument::save()
	 */
	public function save() {
		if (empty($this->document->type)) {
			$this->document->type = get_class($this);
		}

		if (empty($this->validationRules)) {
			throw new Exception("Please define validation rules for document type '{$this->document->type}'.", self::EXCEPTION_MISSING_VALIDATION_RULES);
		}

		$this->controller->validationErrors = $this->validate($this, $this->validationRules);

		if (!empty($this->controller->validationErrors)) {
			throw new Exception("Data did not validate successfully.", self::EXCEPTION_INVALID_DATA);
		}

		return parent::save();
	}

	/**
	 * Sets the whitelist.
	 * @param array $whitelist
	 */
	public function setWhitelist($whitelist) {
		$this->whitelist = $whitelist;
	}

	/**
	 * Merges $thing recursively with the document. Fields in $thing supersede
	 * similarly named fields in the document.
	 * @param array|object $thing
	 */

	/**
	 * Merges $thing recursively with the document. Fields in $thing supersede
	 * similarly named fields in the document. The fields in $thing must be
	 * whitelisted.
	 * @see CouchDbDocument::merge()
	 * @throws Exception
	 */
	public function merge($thing) {
		if (empty($this->whitelist)) {
			throw new Exception("Please set a whitelist before the merge.", self::EXCEPTION_MISSING_WHITELIST);
		}

		$thing = json_decode(json_encode($thing));

		$result = self::checkAgainstWhitelist($thing, $this->whitelist);

		if ($result !== true) {
			throw new Exception("Data contains non-whitelisted field '$result'", self::EXCEPTION_NON_WHITELISTED_FIELD_PRESENT);
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
	 * @throws InvalidArgumentException
	 */
	public function validate($document, $rules) {
		$validationErrors = array();

		foreach ($rules as $fieldName => $rule) {
			if (is_object($document) && !isset($document->$fieldName)) {
				$document->$fieldName = "";
			} elseif (is_array($document) && !array_key_exists($fieldName, $document)) {
				$document[$fieldName] = "";
			}

			$field = is_object($document) ? $document->$fieldName : $document[$fieldName];

			if (isset($rule["rule"])) {
				// The name of the function used to validate the field is passed as string
				if (is_string($rule["rule"])) {
					$validates = $this->$rule["rule"]($field);
				// The name of the function used to validate the field is passed in an array (as first argument)
				} elseif (is_array($rule["rule"])) {
					$arguments = $rule["rule"];
					$arguments[0] = $field;

					$validates = call_user_func_array(array($this, $rule["rule"][0]), $arguments);
				} else {
					throw new InvalidArgumentException("Rule for field '$fieldName' must either be a string or array (" . gettype($rule["rule"]) . " given).");
				}

				if (!$validates) {
					$validationErrors[$fieldName][] = array(
						"message" => !empty($rule["message"]) ? $rule["message"] : "Value for field '$fieldName' is not valid.",
						"value" => $field,
					);
				}
			} elseif (isset($rule["contains"])) {
				$errors = $this->validate($field, $rule["contains"]);

				if (!empty($errors)) {
					$validationErrors[$fieldName] = $errors;
				}
			} else {
				throw new InvalidArgumentException("Validation rule for field '$fieldName' is not formed properly. Please add 'rule' or 'contains'.");
			}
		}

		return $validationErrors;
	}

	public static function isBoolean($value) {
		return is_bool($value);
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