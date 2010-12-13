<?php
class FormHelper extends Helper {
	public $helpers = array("Html");

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
		// Set default name if necessary
		if (!isset($attributes["name"])) {
			$fieldAsArray = $fieldNameSuffix !== "" ? "[]" : "";
			$attributes["name"] = $fieldName . $fieldAsArray;
		}

		// Set default id if necessary
		if (!isset($attributes["id"])) {
			$attributes["id"] = sprintf("%s-input%s", str_replace("_", "-", $fieldName), $fieldNameSuffix);
		}

		// Set default type to "text" if necessary
		if (!isset($attributes["type"])) {
			$attributes["type"] = "text";
		}

		// Auto-populate value attribute if possible
		if (!isset($attributes["value"])
				&& isset($this->data[$fieldName])) {
			$attributes["value"] = $this->data[$fieldName];
		}

		// Create element
		$element = new Element("input", $attributes);
		return $element->toString() . $this->errorMessageList($fieldName);
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

	public function hidden($fieldName, $value = "", $attributes = array(), $fieldNameSuffix = "") {
		$attributes["type"] = "hidden";

		if (!empty($value)) {
			$attributes["value"] = $value;
		}

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

		$elementId = sprintf("%s-input%s", str_replace("_", "-", $fieldName), $fieldNameSuffix);

		$attributes["for"] = $elementId;
		$attributes["html"] = $label;

		$element = new Element("label", $attributes);
		return $element->toString();
	}

	public function select($fieldName, $choices = array(), $attributes = array()) {
		$attributes["name"] = $fieldName;

		if (!isset($attributes["id"])) {
			$attributes["id"] = sprintf("%s-input", str_replace("_", "-", $fieldName));
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
		$attributes["id"] = sprintf("%s-input", str_replace("_", "-", $fieldName));
		$attributes["rows"] = 3;
		$attributes["cols"] = 27;

		if (isset($this->data[$fieldName])) {
			$attributes["html"] = $this->data[$fieldName];
		}

		$element = new Element("textarea", $attributes);
		return $element->toString() . $this->errorMessageList($fieldName);
	}

	public function errorMessageList($fieldName) {
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

			return $errorList->toString();
		}
	}
}
?>