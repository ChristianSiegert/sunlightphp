<?php
namespace Views\Helpers;

use Libraries\Element;
use Libraries\Router;

class Html extends Helper {
	protected $crumbs = array();

	protected $meta = array();

	public static function link($title, $url = null, $attributes = array()) {
		$element = new Element("a", $attributes);
		$element->setHtml($title);

		if ($url !== null) {
			$element->href = is_array($url) ? Router::url($url) : $url;
		}

		return $element;
	}

	public static function image($url, $description = "", $attributes = array()) {
		$element = new Element("img", $attributes);
		$element->alt = $description;
		$element->src = preg_match('#^https?://#', $url) ? $url : BASE_URL . "/$url";
		return $element;
	}

	/**
	 * Creates a link element pointing to the favicon. The icon must reside in
	 * the webroot directory if $url is only a filename.
	 * @param string $url URL or filename of the icon.
	 */
	public static function icon($url, $attributes = array()) {
		$element = new Element("link", $attributes);
		$element->href = preg_match('#^https?://#', $url) ? $url : BASE_URL . "/$url";

		if (!isset($element->rel)) {
			$element->rel = "shortcut icon";
		}

		if (!isset($element->type)) {
			$element->type = "image/x-icon";
		}

		return $element;
	}

	public static function script($code, $attributes = array()) {
		$element = new Element("script", $attributes);
		$element->setHtml("//<![CDATA[\n$code\n//]]>");
		$element->type = "text/javascript";
		return $element;
	}

	public static function scriptLink($url, $attributes = array()) {
		$element = new Element("script", $attributes);
		$element->src = $url;
		$element->type = "text/javascript";
		return $element;
	}

	public static function atom($title, $url, $attributes = array()) {
		$element = new Element("link", $attributes);
		$element->href = is_array($url) ? Router::url($url) : $url;
		$element->rel = "alternate";
		$element->title = $title;
		$element->type = "application/atom+xml";
		return $element;
	}

	public function addCrumb($title, $url = null) {
		$this->crumbs[] = array($title, $url);
	}

	public function getCrumbs($prefix = "Home", $glue = " &rsaquo; ") {
		$numberOfCrumbs = count($this->crumbs);

		$string = $numberOfCrumbs > 0 ? self::link($prefix, BASE_URL . "/") : $prefix;

		for ($i = 0; $i < $numberOfCrumbs; $i++) {
			if ($prefix !== "" || $i > 0) {
				$string .= $glue;
			}

			if ($i < $numberOfCrumbs - 1) {
				$string .= self::link($this->crumbs[$i][0], $this->crumbs[$i][1]);
			} else {
				$string .= $this->crumbs[$i][0];
			}
		}

		return $string;
	}

	public static function meta($attributes) {
		$this->meta[] = $attributes;
	}

	public function metaForLayout() {
		$elements = "";

		foreach ($this->meta as $meta) {
			$element = new Element("meta", $meta);
			$elements .= $element->toString();
		}

		if (!empty($this->view->pageKeywords)) {
			$element = new Element("meta", array(
				"content" => $this->view->pageKeywords,
				"name" => "keywords"
			));

			$elements .= $element->toString();
		}

		return $elements;
	}
}
?>