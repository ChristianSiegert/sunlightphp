<?php
class Document extends CouchDbDocument {
	/**
	 * Contains the validation rules that are used to validate the content of
	 * the document.
	 * @var array
	 */
	public $validationRules = array();

	/**
	 * The controller of the document. Can be any object, usually it is an
	 * instance of "Controller" or "Shell".
	 * @var object
	 */
	protected $controller;

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
	// TODO: Check whitelist
	public function save() {
		if (empty($this->document->type)) {
			$this->document->type = get_class($this);
		}

		if (empty($this->validationRules)) {
			throw new Exception("Please define validation rules for document type '{$this->document->type}'.");
		}

		$this->controller->validationErrors = $this->validate($this, $this->validationRules);

		if (!empty($this->controller->validationErrors)) {
			throw new Exception("Data is not valid.");
		}

		return parent::save();
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