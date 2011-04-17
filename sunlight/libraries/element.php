<?php
namespace Libraries;

class Element {
	protected $tag;
	protected $attributes = array();
	protected $html = "";
	protected $children = array();

	function __construct($tag, $attributes = array()) {
		$this->tag = $tag;

		if (isset($attributes["html"])) {
			$this->html = $attributes["html"];
			unset($attributes["html"]);
		}

		$this->attributes = $attributes;
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
	 * @return string Element in string form
	 */
	public function toString() {
		// Create string for attributes
		$attributes = "";
		foreach ($this->attributes as $attributeName => $attributeValue) {
			$attributes .= " $attributeName=\"$attributeValue\"";
		}

		// Create string for open-tag
		$string = "<{$this->tag}$attributes>{$this->html}";

		// Create string for child elements
		foreach ($this->children as $child) {
			if (is_object($child)) {
				$string .= $child->toString();
			} else {
				$string .= $child;
			}
		}

		// Create string for close-tag
		$string .= "</{$this->tag}>";

		return $string;
	}
}
?>