<?php
class HtmlHelper extends Helper {
	protected $crumbs = array();

	protected $meta = array();

	public function element($tag, $attributes = array(), $ttl = 0) {
		if ($ttl !== false) {
			$cacheKey = "htmlHelper:element:$tag:" . serialize($attributes);
			$element = Cache::fetch($cacheKey, "apcOnly");

			if ($element !== false) {
				return $element;
			}
		}

		$element = new Element($tag, $attributes);
		$element = $element->toString();

		if ($ttl !== false) {
			Cache::store($cacheKey, $element, $ttl, "apcOnly");
		}

		return $element;
	}

	public function addCrumb($title, $url = null) {
		$this->crumbs[] = array($title, $url);
	}

	public function getCrumbs($glue = " &rsaquo; ", $prefix = "Home") {
		$cacheKey = "htmlHelper:getCrumbs:$glue:$prefix:" . serialize($this->crumbs);
		$string = Cache::fetch($cacheKey, "apcOnly");

		if ($string !== false) {
			return $string;
		} else {
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

			Cache::store($cacheKey, $string, 60, "apcOnly");
			return $string;
		}
	}

	public function link($title, $url = "", $attributes = array(), $ttl = 0) {
		$attributes["html"] = $title;
		$attributes["href"] = is_array($url) ? Router::url($url) : $url;

		return $this->element("a", $attributes, $ttl);
	}

	public function image($url, $title = "", $attributes = array(), $ttl = 0) {
		$attributes["src"] = preg_match('#^https?://#', $url) ? $url : BASE_URL . "/img/$url";
		$attributes["title"] = $title;
		$attributes["alt"] = $title;

		return $this->element("img", $attributes, $ttl);
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
		return $this->element("link", array(
			"href" => BASE_URL . "/$filename",
			"rel" => "shortcut icon",
			"type" => "image/x-icon"
		));
	}

	public function script($code, $ttl = 0) {
		return $this->element("script", array(
			"html" => "//<![CDATA[\n$code\n//]]>",
			"type" => "text/javascript"
		), $ttl);
	}

	public function scriptLink($url) {
		return $this->element("script", array(
			"src" => $url,
			"type" => "text/javascript"
		));
	}

	public function atom($title, $url) {
		return $this->element("link", array(
			"href" => Router::url($url),
			"rel" => "alternate",
			"title" => $title,
			"type" => "application/atom+xml"
		));
	}
}
?>