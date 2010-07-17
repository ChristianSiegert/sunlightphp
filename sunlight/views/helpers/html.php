<?php
class HtmlHelper extends Helper {
	protected $crumbs = array();

	public function element($tag, $options = array(), $format = "") {
		$cacheKey = "htmlHelper:element:$tag:" . serialize($options) . ":$format";
		$element = Cache::fetch($cacheKey, "apcOnly");

		if ($element !== false) {
			return $element;
		}

		$element = new Element($tag, $options);
		$element = $element->toString($format);

		Cache::store($cacheKey, $element, 0, "apcOnly");

		return $element;
	}

	public function addCrumb($title, $url = null) {
		$this->crumbs[] = array($title, $url);
	}

	public function getCrumbs($glue = " &raquo; ", $prefix = "Home") {
		$cacheKey = "htmlHelper:getCrumbs:$glue:$prefix:" . serialize($this->crumbs);
		$string = Cache::fetch($cacheKey, "apcOnly");

		if ($string !== false) {
			return $string;
		}

		if (count($this->crumbs) > 0) {
			$string = $this->link($prefix, BASE_URL . "/");
		} else {
			$string = $prefix;
		}

		$numberOfCrumbs = count($this->crumbs);
		for ($i = 0; $i < $numberOfCrumbs; $i++) {
			if ($i < $numberOfCrumbs - 1) {
				$string .= $glue . $this->link($this->crumbs[$i][0], $this->crumbs[$i][1]);
			} else {
				$string .= $glue . $this->crumbs[$i][0];
			}
		}

		Cache::store($cacheKey, $string, 0, "apcOnly");

		return $string;
	}

	public function link($title, $url, $options = array()) {
		$options["html"] = $title;
		$options["href"] = Router::url($url);

		return $this->element("a", $options);
	}

	public function image($url, $title = "", $options = array()) {
		if (preg_match('#^https?://#', $url) === 1) {
			$options["src"] = $url;
		} else {
			$options["src"] = BASE_URL . "/img/$url";
		}

		$options["title"] = $title;
		$options["alt"] = $title;

		return $this->element("img", $options);
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
}
?>