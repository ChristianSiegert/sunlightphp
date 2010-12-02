<?php
class HtmlHelper extends Helper {
	protected $crumbs = array();

	protected $meta = array();

	public function addCrumb($title, $url = null) {
		$this->crumbs[] = array($title, $url);
	}

	public function getCrumbs($glue = " &rsaquo; ", $prefix = "Home") {
		$numberOfCrumbs = count($this->crumbs);

		$string = $numberOfCrumbs > 0 ? $this->link($prefix, BASE_URL . "/") : $prefix;

		for ($i = 0; $i < $numberOfCrumbs; $i++) {
			if ($prefix !== "" || $i > 0) {
				$string .= $glue;
			}

			if ($i < $numberOfCrumbs - 1) {
				$string .= $this->link($this->crumbs[$i][0], $this->crumbs[$i][1], array(), 60);
			} else {
				$string .= $this->crumbs[$i][0];
			}
		}

		return $string;
	}

	public function link($title, $url = "", $attributes = array()) {
		$attributes["html"] = $title;
		$attributes["href"] = is_array($url) ? Router::url($url) : $url;

		$element = new Element("a", $attributes);
		return $element->toString();
	}

	public function image($url, $title = "", $attributes = array()) {
		$attributes["src"] = preg_match('#^https?://#', $url) ? $url : BASE_URL . "/img/$url";
		$attributes["title"] = $title;
		$attributes["alt"] = $title;

		$element = new Element("img", $attributes);
		return $element->toString();
	}

	public function meta($attributes) {
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

	/**
	 * Creates a link element pointing to the favicon. The icon must reside in
	 * the webroot directory.
	 *
	 * @param string $filename Name of the icon file
	 */
	public function icon($filename) {
		$element = new Element("link", array(
			"href" => BASE_URL . "/$filename",
			"rel" => "shortcut icon",
			"type" => "image/x-icon"
		));

		return $element->toString();
	}

	public function script($code) {
		$element = new Element("script", array(
			"html" => "//<![CDATA[\n$code\n//]]>",
			"type" => "text/javascript"
		));

		return $element->toString();
	}

	public function scriptLink($url) {
		$element = new Element("script", array(
			"src" => $url,
			"type" => "text/javascript"
		));

		return $element->toString();
	}

	public function atom($title, $url) {
		$element = new Element("link", array(
			"href" => Router::url($url),
			"rel" => "alternate",
			"title" => $title,
			"type" => "application/atom+xml"
		));

		return $element->toString();
	}
}
?>