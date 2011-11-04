<?php
namespace Libraries;

class Element {
	protected $tag;
	protected $attributes = array();
	protected $html = "";
	protected $children = array();

	/**
	 * Constructs an element.
	 * @param string $tag
	 * @param array $attributes
	 * @param string $html
	 * @return \Libraries\Element
	 */
	function __construct($tag, array $attributes = array(), $html = "") {
		$this->tag = $tag;
		$this->attributes = $attributes;
		$this->html = $html;
		return $this;
	}

	/**
	 * Returns an attribute.
	 * @param string $attributeName
	 * @return mixed|null Returns attribute if it exists, otherwise null
	 */
	public function __get($attributeName) {
		return isset($this->attributes[$attributeName]) ? $this->attributes[$attributeName] : null;
	}

	/**
	 * Sets an attribute.
	 * @param string $attributeName
	 * @param mixed $attributeValue
	 */
	public function __set($attributeName, $attributeValue) {
		$this->attributes[$attributeName] = $attributeValue;
	}

	/**
	 * Deletes an attribute
	 * @param string $attributeName
	 */
	public function __unset($attributeName) {
		unset($this->attributes[$attributeName]);
	}

	/**
	 * Checks if an attribute is set.
	 * @param string $attributeName
	 * @return boolean
	 */
	public function __isset($attributeName) {
		return isset($this->attributes[$attributeName]);
	}

	/**
	 * Returns the element as string.
	 * @return string Element.
	 */
	public function __toString() {
		return $this->toString();
	}

	/**
	 * Injects this element into another element (means this element becomes a
	 * child element of the other element).
	 * @param \Libraries\Element $parent
	 */
	public function inject(Element $parent) {
		$parent->children[] = $this;
	}

	/**
	 * Grabs an element and makes it a child of this element.
	 * @param \Libraries\Element $child
	 */
	public function grab(Element $child) {
		$this->children[] = $child;
	}

	/**
	 * Returns the HTML of this element (the part between the opening and
	 * closing tag; does not include any child elements).
	 */
	public function getHtml() {
		return $this->html;
	}

	/**
	 * Sets the HTML of this element (the part between the opening and closing
	 * tag; does not interfere with child elements).
	 * @param string $html
	 */
	public function setHtml($html) {
		$this->html = $html;
	}

	/**
	 * Returns the element as string.
	 * @return string Element in string form
	 */
	public function toString() {
		// Create string with attributes
		$attributes = "";
		foreach ($this->attributes as $attributeName => $attributeValue) {
			$attributes .= " $attributeName=\"$attributeValue\"";
		}

		// Create open-tag with attributes and HTML
		$string = "<{$this->tag}$attributes>{$this->html}";

		// Append stringified child elements
		$string .= implode("", $this->children);

		// Append close-tag
		$string .= "</{$this->tag}>";

		return $string;
	}

	/**
	 * Adds a CSS class to the element.
	 * @return \Libraries\Element
	 */
	public function addClass($className) {
		if (!isset($this->attributes["class"])) {
			$this->attributes["class"] = $className;
			return;
		}

		$this->attributes["class"] .= " $className";
		return $this;
	}

	/**
	 * Removes a CSS class from the element.
	 * @return \Libraries\Element
	 */
	public function removeClass($className) {
		if (!isset($this->attributes["class"])) return $this;

		$classes = explode(" ", $this->attributes["class"]);
		$classCount = count($classes);

		for ($i = 0; $i < $classCount; $i++) {
			if ($classes[$i] === $className) {
				array_splice($classes, $i, 1);
				break;
			}
		}

		$this->attributes["class"] = implode(" ", $classes);
		if ($this->attributes["class"] === "") unset($this->attributes["class"]);
		return $this;
	}

	/**
	 * Checks if the element has a certain CSS class.
	 * @return boolean True if element has class, otherwise false
	 */
	public function hasClass($className) {
		if (!isset($this->attributes["class"])) return false;

		$classes = explode(" ", $this->attributes["class"]);
		return in_array($className, $classes);
	}
}
?>