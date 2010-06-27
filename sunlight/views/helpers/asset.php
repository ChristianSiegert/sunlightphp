<?php
class AssetHelper extends Helper {
	protected $assets = array(
		"css" => array(),
		"js" => array()
	);

	protected $assetsTop = array(
		"css" => array(),
		"js" => array()
	);

	public function css($filename, $options = array()) {
		if (is_array($options) === false) {
			$options = array($options);
		}

		if (in_array("top", $options)) {
			array_push($this->assetsTop["css"], $filename);
		} else {
			array_push($this->assets["css"], $filename);
		}
	}

	public function js($filename, $options = array()) {
		if (is_array($options) === false) {
			$options = array($options);
		}

		if (in_array("top", $options)) {
			array_push($this->assetsTop["js"], $filename);
		} else {
			array_push($this->assets["js"], $filename);
		}
	}

	/**
	 * Returns HTML linking to or inlining CSS (depending on CSS cache setting).
	 *
	 * @return string <link> or <style> tag
	 */
	public function cssForLayout() {
		if (CACHE_CSS) {
			$cacheKey = "asset:cssForLayout:" . serialize($this->assetsTop["css"]) . ":" . serialize($this->assets["css"]);
			$element = Cache::fetch($cacheKey, "apcOnly");

			if ($element !== false) {
				return $element;
			}

			$cacheFilename = md5(serialize($this->assetsTop["css"]) . "_" . serialize($this->assets["css"])) . "_" . URL_SALT . ".css";
			$file = CCSS_DIR . DS . $cacheFilename;

			if (!file_exists($file)) {
				if (COMPRESS_CSS) {
					$code = $this->compress($this->getMergedCode("css"), "css");
				} else {
					$code = $this->getMergedCode("css");
				}

				file_put_contents($file, $code);
			}

			$element = new Element("link", array(
				"href" => CCSS_URL . "/" . $cacheFilename,
				"rel" => "stylesheet",
				"type" => "text/css"
			));
			$element = $element->toString();

			Cache::store($cacheKey, $element, 0, "apcOnly");
			return $element;
		} else {
			if (COMPRESS_CSS) {
				$code = $this->compress($this->getMergedCode("css"), "css");
			} else {
				$code = $this->getMergedCode("css");
			}

			// Fix relative paths in stylesheets
			$code = mb_ereg_replace("url(?:\s*)\(\.\./", "url(" . BASE_URL . "/", $code, "i");

			$element = new Element("style", array(
				"html" => $code,
				"type" => "text/css"
			));

			return $element->toString();
		}
	}

	/**
	 * Returns HTML linking to or inlining JS (depending on JS cache setting).
	 *
	 * @return string <script> tag
	 */
	public function jsForLayout() {
		if (CACHE_JS) {
			$cacheKey = "asset:jsForLayout:" . serialize($this->assetsTop["js"]) . ":" . serialize($this->assets["js"]);
			$element = Cache::fetch($cacheKey, "apcOnly");

			if ($element !== false) {
				return $element;
			}

			$cacheFilename = md5(serialize($this->assetsTop["js"]) . "_" . serialize($this->assets["js"])) . "_" . URL_SALT . ".js";
			$file = CJS_DIR . DS . $cacheFilename;

			if (!file_exists($file)) {
				if (COMPRESS_JS) {
					$code = $this->compress($this->getMergedCode("js"), "js");
				} else {
					$code = $this->getMergedCode("js");
				}

				file_put_contents($file, $code);
			}

			$element = new Element("script", array(
				"src" => CJS_URL . "/" . $cacheFilename,
				"type" => "text/javascript"
			));
			$element = $element->toString();

			Cache::store($cacheKey, $element, 0, "apcOnly");
			return $element;
		} else {
			if (COMPRESS_JS) {
				$code = $this->compress($this->getMergedCode("js"), "js");
			} else {
				$code = $this->getMergedCode("js");
			}

			$element = new Element("script", array(
				"html" => $code,
				"type" => "text/javascript"
			));

			return $element->toString();
		}
	}

	protected function getMergedCode($type) {
		$mergedCode = "";

		foreach ($this->assetsTop[$type] as $filename) {
			$filepath = WEBROOT_DIR . DS . $type . DS . $filename;

			if (file_exists($filepath)) {
				$mergedCode .= file_get_contents($filepath);
			}
		}

		foreach ($this->assets[$type] as $filename) {
			$filepath = WEBROOT_DIR . DS . $type . DS . $filename;

			if (file_exists($filepath)) {
				$mergedCode .= file_get_contents($filepath);
			}
		}

		return $mergedCode;
	}

	/**
	 * Compresses CSS and JS code.
	 *
	 * Warning: Needs a looong time. Cache the result! Avoid this method at all costs.
	 *
	 * @param string $code
	 * @param string $type (either "css" or "js")
	 * @return string Compressed code
	 */
	protected function compress($code, $type) {
		$command = "java -jar " . VENDOR_DIR . DS . "yuicompressor" . DS . "yuicompressor-2.4.2.jar --type $type";

		$descriptor = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);

		$process = proc_open($command, $descriptor, $pipes);

		if (is_resource($process)) {
			fwrite($pipes[0], $code);
			fclose($pipes[0]);

			$compressedCode = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			fclose($pipes[2]);
			proc_close($process);

			return $compressedCode;
		}
	}
}
?>