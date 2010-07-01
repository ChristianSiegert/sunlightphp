<?php
class FormHelper extends Helper {
	public $helpers = array("Html");

	protected $defaultModel;

	public function __construct(&$view) {
		parent::__construct(&$view);
		$this->defaultModel = ucfirst(Inflector::singularize($this->params["controller"]));
	}

	public function element($tag, $options = array(), $format = "") {
		return $this->view->helperObjects["html"]->element($tag, $options, $format);
	}

	public function create($options = array()) {
		$options["accept-charset"] = "utf-8";
		$options["method"] = "post";

		if (!isset($options["action"])) {
			$options["action"] = "";
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
			$options["name"] =  "data[" . $this->defaultModel . "][" . mb_strtolower($label) . "]";
		}

		if ($value !== null) {
			$options["value"] = $value;
		}

		return $this->element("button", $options);
	}

	public function cancel($label = "Cancel", $value = ".", $options = array()) {
		$options["name"] = "cancel";

		if (is_array($value)) {
			$value = $this->url($value);
		}

		return $this->button($label, $value, $options);
	}

	public function input($fieldName, $options = array(), $fieldSuffix = null) {
		// Set name attribute
		$fieldAsArray = $fieldSuffix === null ? "" : "[]";
		$options["name"] = "data[" . $this->defaultModel . "][$fieldName]$fieldAsArray";

		// Set default id if necessary
		if (!isset($options["id"])) {
			$options["id"] = sprintf("%s-%s-input%s", Inflector::singularize($this->params["controller"]), str_replace("_", "-", $fieldName), $fieldSuffix);
		}

		// Set default type to "textbox" if necessary
		if (!isset($options["type"])) {
			$options["type"] = "text";
		}

		// Auto-populate value attribute if possible
		if (isset($this->data[$fieldName])) {
			$options["value"] = $this->data[$fieldName];
		}

		// Create element
		$inputElement = $this->element("input", $options);

		// Error messages
		if (isset($this->validationErrors[$this->defaultModel][$fieldName])) {
			$errorList = new Element("ul", array(
				"class" => "form-error-list"
			));

			foreach ($this->validationErrors[$this->defaultModel][$fieldName] as $errorMessage) {
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

	public function checkbox($field, $value, $options = array(), $fieldSuffix = null) {
		$options["value"] = $value;
		$options["type"] = "checkbox";
		return $this->input($field, $options, $fieldSuffix);
	}

	public function label($field, $label = null, $fieldSuffix = null) {
		$elementId = sprintf("%s-%s-input%s", Inflector::singularize($this->params["controller"]), str_replace("_", "-", $field), $fieldSuffix);

		if ($label === null) {
			$label = ucfirst(mb_ereg_replace("_", " ", $field));
		}

		return $this->element("label", array(
			"for" => $elementId,
			"html" => $label
		));
	}

	public function select($fieldName, $choices = array(), $options = array()) {
		// Set name attribute
		$options["name"] = "data[" . $this->defaultModel . "][$fieldName]";

		// Set default id if necessary
		if (!isset($options["id"])) {
			$options["id"] = sprintf("%s-%s-input", Inflector::singularize($this->params["controller"]), str_replace("_", "-", $fieldName));
		}

		// Create element
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
		$options["name"] = "data[" . $this->defaultModel . "][$fieldName]";
		$options["id"] = sprintf("%s-%s-input", Inflector::singularize($this->params["controller"]), str_replace("_", "-", $fieldName));

		if (isset($this->data[$fieldName])) {
			$options["html"] = $this->data[$fieldName];
		}

		return $this->element("textarea", $options);
	}
}
?>