<?php
class FormHelper extends Helper {
	public $helpers = array("Html");

	public function element($tag, $options = array(), $format = "") {
		return $this->view->helperObjects["html"]->element($tag, $options, $format);
	}

	public function create($options = array()) {
		$options["accept-charset"] = mb_internal_encoding();

		if (!isset($options["method"])) {
			$options["method"] = "post";
		}

		if (!isset($options["action"])) {
			$options["action"] = "";
		} elseif (is_array($options["action"])) {
			$options["action"] = Router::url($options["action"]);
		}

		return $this->element("form", $options, "noEndTag");
	}

	public function end() {
		return "</form>";
	}

	public function submit($label, $options = array()) {
		$options["value"] = $label;
		$options["type"] = "submit";

		return $this->element("input", $options);
	}

	public function button($label, $value = null, $options = array()) {
		$options["html"] = $label;

		if (!isset($options["name"])) {
			$options["name"] =  mb_strtolower($label);
		}

		if ($value !== null) {
			$options["value"] = $value;
		}

		return $this->element("button", $options);
	}

	public function redirect($label, $redirectUrl = array("action" => "index"), $options = array()) {
		$options["name"] = "system[redirectUrl]";

		if (is_array($redirectUrl)) {
			$redirectUrl = Router::url($redirectUrl);
		}

		return $this->button($label, $redirectUrl, $options);
	}

	public function input($fieldName, $options = array(), $fieldNameSuffix = null) {
		// Set default name if necessary
		if (!isset($options["name"])) {
			$fieldAsArray = $fieldNameSuffix === null ? "" : "[]";
			$options["name"] = $fieldName . $fieldAsArray;
		}

		// Set default id if necessary
		if (!isset($options["id"])) {
			$options["id"] = sprintf("%s-input%s", str_replace("_", "-", $fieldName), $fieldNameSuffix);
		}

		// Set default type to "text" if necessary
		if (!isset($options["type"])) {
			$options["type"] = "text";
		}

		// Auto-populate value attribute if possible
		if (!isset($options["value"])
				&& isset($this->data[$fieldName])) {
			$options["value"] = $this->data[$fieldName];
		}

		// Create element
		$inputElement = $this->element("input", $options);

		// Error messages
		if (isset($this->validationErrors[$fieldName])) {
			$errorList = new Element("ul", array(
				"class" => "form-error-list"
			));

			foreach ($this->validationErrors[$fieldName] as $errorMessage) {
				$listItem = new Element("li", array(
					"class" => "form-error-list-item",
					"html" => $errorMessage
				));

				$listItem->inject($errorList);
			}

			return $inputElement . $errorList->toString();
		}

		return $inputElement;
	}

	public function text($fieldName, $options = array(), $fieldNameSuffix = null) {
		$options["type"] = "text";
		return $this->input($fieldName, $options, $fieldNameSuffix);
	}

	public function checkbox($fieldName, $value = "on", $options = array(), $fieldNameSuffix = null) {
		$options["type"] = "checkbox";
		$options["value"] = $value;

		if (isset($this->data[$fieldName])) {
			$options["checked"] = "checked";
		}

		return $this->input($fieldName, $options, $fieldNameSuffix);
	}

	public function hidden($fieldName, $value, $options = array(), $fieldNameSuffix = null) {
		$options["type"] = "hidden";
		$options["value"] = $value;
		return $this->input($fieldName, $options, $fieldNameSuffix);
	}

	public function label($fieldName, $label = null, $fieldNameSuffix = null) {
		if ($label === null) {
			$label = ucfirst(preg_replace('/_/', " ", $fieldName));
		}

		$elementId = sprintf("%s-input%s", str_replace("_", "-", $fieldName), $fieldNameSuffix);

		return $this->element("label", array(
			"for" => $elementId,
			"html" => $label
		));
	}

	public function select($fieldName, $choices = array(), $options = array()) {
		$options["name"] = $fieldName;

		if (!isset($options["id"])) {
			$options["id"] = sprintf("%s-input", str_replace("_", "-", $fieldName));
		}

		$selectElement = new Element("select", $options);

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

	public function textarea($fieldName, $options = array()) {
		$options["name"] = $fieldName;
		$options["id"] = sprintf("%s-input", str_replace("_", "-", $fieldName));

		if (isset($this->data[$fieldName])) {
			$options["html"] = $this->data[$fieldName];
		}

		return $this->element("textarea", $options);
	}
}
?>