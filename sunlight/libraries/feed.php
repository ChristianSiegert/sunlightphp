<?php
namespace Libraries;

use \Exception;

class Feed {
	public function atom($feedElements, $posts) {
		if (!isset($feedElements["id"])) {
			throw new Exception("Atom feed requires an 'id' element.");
		}

		if (!isset($feedElements["title"])) {
			throw new Exception("Atom feed requires a 'title' element.");
		}

		if (!isset($feedElements["updated"])) {
			throw new Exception("Atom feed requires an 'updated' element.");
		}

		$output = '<?xml version="1.0" encoding="' . mb_internal_encoding() . '"?>';

		$feed = new Element("feed", array(
			"xmlns" => "http://www.w3.org/2005/Atom"
		));

		foreach ($feedElements as $tag => $attributes) {
			if (!is_array($attributes)) {
				$attributes = array("html" => $attributes);
			}

			if (isset($attributes["type"]) && $attributes["type"] !== "text") {
				$attributes["html"] = "<![CDATA[" . $attributes["html"] . "]]>";
			}

			$element = new Element($tag, $attributes);
			$element->inject($feed);
		}

		foreach ($posts as $post) {
			$entry = new Element("entry");

			foreach ($post as $tag => $attributes) {
				if (!is_array($attributes)) {
					$attributes = array("html" => $attributes);
				}

				if (isset($attributes["type"]) && $attributes["type"] !== "text") {
					$attributes["html"] = "<![CDATA[" . $attributes["html"] . "]]>";
				}

				$element = new Element($tag, $attributes);
				$element->inject($entry);
			}

			$entry->inject($feed);
		}

		// Set header (Overwrites any previous Content-Type header)
		header("Content-Type: application/atom+xml");

		// Create entries
		return $output . $feed->toString();
	}
}
?>