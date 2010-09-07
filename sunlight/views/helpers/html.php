<?php
class HtmlHelper extends Helper {
	protected $crumbs = array();

	public function element($tag, $options = array(), $format = "", $ttl = 0) {
		if ($ttl !== false) {
			$cacheKey = "htmlHelper:element:$tag:" . serialize($options) . ":$format";
			$element = Cache::fetch($cacheKey, "apcOnly");

			if ($element !== false) {
				return $element;
			}
		}

		$element = new Element($tag, $options);
		$element = $element->toString($format);

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

	public function link($title, $url = "", $options = array(), $ttl = 0) {
		$options["html"] = $title;
		$options["href"] = is_array($url) ? Router::url($url) : $url;

		return $this->element("a", $options, "", $ttl);
	}

	public function image($url, $title = "", $options = array(), $ttl = 0) {
		$options["src"] = preg_match('#^https?://#', $url) ? $url : BASE_URL . "/img/$url";
		$options["title"] = $title;
		$options["alt"] = $title;

		return $this->element("img", $options, "emptyTag", $ttl);
	}

	public function metaForLayout() {
		$elements = "";

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
	 * @param array $options Attributes of the element
	 */
	public function icon($filename) {
		return $this->element("link", array(
			"href" => BASE_URL . "/$filename",
			"rel" => "shortcut icon",
			"type" => "image/x-icon"
		));
	}

	public function script($code) {
		return $this->element("script", array(
			"html" => "//<![CDATA[\n$code\n//]]>",
			"type" => "text/javascript"
		), "", false);
	}
}
?>