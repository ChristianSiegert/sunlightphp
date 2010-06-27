<?php
class Element {
	public $tag;
	public $attributes = array();
	public $html = "";
	public $children = array();

	function __construct($tag, $options = array()) {
		$this->tag = $tag;

		foreach ($options as $key => $option) {
			if ($key === "html") {
				$this->html = $option;
			} else {
				$this->attributes[$key] = $option;
			}
		}

		return $this;
	}

	public function inject($parent) {
		$parent->children[] = $this;
	}

	public function grab($child) {
		$this->children[] = $child;
	}

	/**
	 * Returns element as string.
	 *
	 * @param string $option Nothing or "noEndTag"
	 * @return string Element in string form
	 */
	public function toString($option = null) {
		// Create string for attributes
		$attributes = "";
		foreach ($this->attributes as $attribute => $value) {
			$attributes .= " $attribute=\"$value\"";
		}

		// Create string for open-tag
		$string = "<" . $this->tag . $attributes . ">" . $this->html;

		// Create string for children elements
		foreach ($this->children as $child) {
			if (is_object($child)) {
				$string .= $child->toString($option);
			} else {
				$string .= $child;
			}
		}

		// Create string for close-tag
		if ($option !== "noEndTag") {
			$string .= "</" . $this->tag . ">";
		}

		return $string;
	}
}
?>