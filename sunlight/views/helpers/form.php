<?php
namespace Views\Helpers;

use \Exception as Exception;
use \Libraries\Element as Element;
use \Libraries\Router as Router;
use \Libraries\Sanitizer as Sanitizer;

class Form extends Helper {
	protected function sanitizeFieldName($fieldName) {
		return trim(preg_replace('#[^a-z0-9\-]#', "-", mb_convert_case($fieldName, MB_CASE_LOWER)), "-");
	}

	public function create($attributes = array()) {
		$attributes["accept-charset"] = mb_internal_encoding();

		if (!isset($attributes["method"])) {
			$attributes["method"] = "post";
		}

		if (!isset($attributes["action"])) {
			$attributes["action"] = "";
		} elseif (is_array($attributes["action"])) {
			$attributes["action"] = Router::url($attributes["action"]);
		}

		$form = new Element("form", $attributes);
		$form = $form->toString();

		return preg_replace('#</form>$#', "", $form);
	}

	public function end() {
		return "</form>";
	}

	public function submit($label, $attributes = array()) {
		$attributes["value"] = $label;
		$attributes["type"] = "submit";

		$element = new Element("input", $attributes);
		return $element->toString();
	}

	public function button($label, $value = null, $attributes = array()) {
		$attributes["html"] = $label;

		if (!isset($attributes["name"])) {
			$attributes["name"] =  mb_strtolower($label);
		}

		if ($value !== null) {
			$attributes["value"] = $value;
		}

		$element = new Element("button", $attributes);
		return $element->toString();
	}

	public function redirect($label, $redirectUrl = array("action" => "index"), $attributes = array()) {
		$attributes["name"] = "system[redirectUrl]";

		if (is_array($redirectUrl)) {
			$redirectUrl = Router::url($redirectUrl);
		}

		return $this->button($label, $redirectUrl, $attributes);
	}

	public function input($fieldName, $attributes = array(), $fieldNameSuffix = "") {
		// Set default type to "text" if necessary
		if (!isset($attributes["type"])) {
			$attributes["type"] = "text";
		}

		// Set default name if necessary
		if (!isset($attributes["name"])) {
			$fieldAsArray = $fieldNameSuffix !== "" ? "[]" : "";
			$attributes["name"] = $fieldName . $fieldAsArray;
		}

		// Set default id if necessary
		if (!isset($attributes["id"])) {
			$attributes["id"] = $this->sanitizeFieldName($fieldName) . "-input" . $fieldNameSuffix;
		}

		// Set maxlength attribute if possible
		if ($attributes["name"] === "e_mail_address"
				&& !isset($attributes["maxlength"])
				&& $attributes["type"] === "text") {
			$attributes["maxlength"] = 254;
		}

		// Auto-populate value attribute if possible
		if (!isset($attributes["value"])) {
			try {
				$attributes["value"] = Sanitizer::encodeHtml($this->getValueByFieldName($fieldName));
			} catch (Exception $exception) {}
		}

		// Get HTML list with all validation errors for this field
		$errorMessageList = $this->errorMessageList($fieldName);

		// Highlight field if it has any validation errors
		if (!empty($errorMessageList)) {
			$attributes["class"] = isset($attributes["class"]) ? $attributes["class"] . " has-validation-errors" : "has-validation-errors";
		}

		// Create element
		$element = new Element("input", $attributes);
		return $element->toString() . $errorMessageList;
	}

	public function checkbox($fieldName, $value = "on", $attributes = array(), $fieldNameSuffix = "") {
		$attributes["type"] = "checkbox";
		$attributes["value"] = $value;

		if (isset($this->data[$fieldName])
				&& $this->data[$fieldName] === true
				&& (empty($fieldNameSuffix) || in_array($value, $this->data[$fieldName]))) {
			$attributes["checked"] = "checked";
		}

		return $this->input($fieldName, $attributes, $fieldNameSuffix);
	}

	public function radio($fieldName, $value = "on", $attributes = array(), $fieldNameSuffix = "") {
		$attributes["type"] = "radio";
		return $this->input($fieldName, $attributes, $fieldNameSuffix);
	}

	public function hidden($fieldName, $value = "", $attributes = array(), $fieldNameSuffix = "") {
		$attributes["type"] = "hidden";

		if (!empty($value)) {
			$attributes["value"] = $value;
		}

		return $this->input($fieldName, $attributes, $fieldNameSuffix);
	}

	public function file($fieldName = "file", $attributes = array(), $fieldNameSuffix = "") {
		$attributes["type"] = "file";
		return $this->input($fieldName, $attributes, $fieldNameSuffix);
	}

	public function password($fieldName = "password", $attributes = array(), $fieldNameSuffix = "") {
		$attributes["type"] = "password";
		return $this->input($fieldName, $attributes, $fieldNameSuffix);
	}

	public function text($fieldName, $attributes = array(), $fieldNameSuffix = "") {
		$attributes["type"] = "text";
		return $this->input($fieldName, $attributes, $fieldNameSuffix);
	}

	public function label($fieldName, $label = "", $attributes = array(), $fieldNameSuffix = "") {
		if (empty($label)) {
			$label = ucfirst(preg_replace('/_/', " ", $fieldName));
		}

		$attributes["for"] = $this->sanitizeFieldName($fieldName) . "-input" . $fieldNameSuffix;
		$attributes["html"] = $label;

		$element = new Element("label", $attributes);
		return $element->toString();
	}

	public function select($fieldName, $choices = array(), $attributes = array()) {
		$attributes["name"] = $fieldName;

		if (!isset($attributes["id"])) {
			$attributes["id"] = $this->sanitizeFieldName($fieldName) . "-input";
		}

		$selectElement = new Element("select", $attributes);

		// Fill element with provided choices
		foreach ($choices as $value => $label) {
			$choice = new Element("option", array(
				"html" => $label,
				"value" => $value
			));

			// Pre-select choice
			if (isset($this->data[$fieldName])
					&& $this->data[$fieldName] == $value) {
				$choice->attributes["selected"] = "selected";
			}

			$selectElement->grab($choice);
		}

		return $selectElement->toString();
	}

	public function textarea($fieldName, $attributes = array()) {
		$attributes["name"] = $fieldName;
		$attributes["id"] = $this->sanitizeFieldName($fieldName) . "-input";
		$attributes["rows"] = 3;
		$attributes["cols"] = 27;

		if (!isset($attributes["html"])) {
			try {
				$attributes["html"] = Sanitizer::encodeHtml($this->getValueByFieldName($fieldName));
			} catch (Exception $exception) {}
		}

		// Get HTML list with all validation errors for this field
		$errorMessageList = $this->errorMessageList($fieldName);

		// Highlight field if it has any validation errors
		if (!empty($errorMessageList)) {
			$attributes["class"] = isset($attributes["class"]) ? $attributes["class"] . " has-validation-errors" : "has-validation-errors";
		}

		$element = new Element("textarea", $attributes);
		return $element->toString() . $errorMessageList;
	}

	/**
	 * Returns an HTML list that contains a field's validation error messages.
	 * @param string $fieldName
	 * @return string|null HTML list if field has any validation errors, otherwise null
	 */
	public function errorMessageList($fieldName) {
		$validationErrors = $this->getValidationErrorsByFieldName($fieldName);

		if (!empty($validationErrors)) {
			$errorList = new Element("ul", array(
				"class" => "form-error-list"
			));

			foreach ($this->getValidationErrorsByFieldName($fieldName) as $error) {
				$listItem = new Element("li", array(
					"class" => "form-error-list-item",
					"html" => $error["message"]
				));

				$listItem->inject($errorList);
			}

			return $errorList->toString();
		}
	}

	/**
	 * Returns an array that contains a field's validation errors.
	 * @param string $fieldName
	 * @return array A field's validation errors
	 */
	protected function getValidationErrorsByFieldName($fieldName) {
		$validationErrors = array();
		$allValidationErrors = $this->validationErrors;

		// Convert HTML field name like "address[foo][bar]" into associative array
		parse_str($fieldName, $fieldNames);

		$fieldNames = $this->getFieldNameList($fieldNames);

		foreach ($fieldNames as $fieldName) {
			if (isset($allValidationErrors[$fieldName])) {
				$validationErrors = $allValidationErrors = $allValidationErrors[$fieldName];
			} else {
				return array();
			}
		}

		return $validationErrors;
	}

	/**
	 * Returns the value for an HTML element by searching in $this->data.
	 * @param string $fieldName The HTML element's name, e.g. "description" or "address[foo][bar]"
	 * @return mixed The found value
	 * @throws Exception if $this->data does not contain the field
	 */
	protected function getValueByFieldName($fieldName) {
		$value = "";
		$data = $this->data;

		// Convert HTML field name like "address[foo][bar]" into associative array
		parse_str($fieldName, $fieldNames);

		$fieldNames = $this->getFieldNameList($fieldNames);

		foreach ($fieldNames as $fieldName) {
			if (isset($data[$fieldName])) {
				$value = $data = $data[$fieldName];
			} else {
				throw new Exception('$this->data cannot provide any data for this field.');
			}
		}

		return $value;
	}

	/**
	 * Returns an indexed array containing the keys of a multidimensional
	 * associative array.
	 * @param array $array (Multidimensional) associative array
	 * @return array The keys of the (multidimensional) associative array
	 */
	protected function getFieldNameList($array) {
		$keys = array();

		foreach ($array as $key => $value) {
			$keys[] = $key;

			if (is_array($value)) {
				$keys = array_merge($keys, $this->getFieldNameList($value));
			}
		}

		return $keys;
	}
}
?>