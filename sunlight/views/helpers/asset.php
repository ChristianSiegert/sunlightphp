<?php
namespace Views\Helpers;

use Libraries\Cache;
use Libraries\Config;
use Libraries\Element;
use Libraries\Log;

class Asset extends Helper {
	protected static $assets = array(
		"css" => array(),
		"js" => array()
	);

	protected static $assetsTop = array(
		"css" => array(),
		"js" => array()
	);

	protected static $standaloneAssets = array(
		"css" => array(),
		"js" => array()
	);

	public static function css($filename, $options = array()) {
		if (!is_array($options)) {
			$options = array($options);
		}

		if (in_array("standalone", $options)) {
			array_push(self::$standaloneAssets["css"], $filename);
		} elseif (in_array("top", $options)) {
			array_push(self::$assetsTop["css"], $filename);
		} else {
			array_push(self::$assets["css"], $filename);
		}
	}

	public static function js($filename, $options = array()) {
		if (!is_array($options)) {
			$options = array($options);
		}

		if (in_array("standalone", $options)) {
			array_push(self::$standaloneAssets["js"], $filename);
		} elseif (in_array("top", $options)) {
			array_push(self::$assetsTop["js"], $filename);
		} else {
			array_push(self::$assets["js"], $filename);
		}
	}

	/**
	 * Returns HTML code inlining CSS code and/or linking to CSS files (depending on CSS cache setting).
	 * @return string <style> and/or <link> tags
	 */
	public static function cssForLayout() {
		if (!Config::read("debug")) {
			$cacheKey = "assetHelper:cssForLayout:" . serialize(self::$standaloneAssets["css"]) . ":" . serialize(self::$assetsTop["css"]) . ":" . serialize(self::$assets["css"]);
			$cssForLayout = Cache::fetch($cacheKey, "apcOnly");

			if ($cssForLayout !== false) {
				return $cssForLayout;
			}
		}

		$cssForLayout = "";

		foreach (self::$standaloneAssets["css"] as $filename) {
			$element = new Element("link", array(
				"href" => BASE_URL . "/css/$filename",
				"rel" => "stylesheet",
				"type" => "text/css"
			));

			$cssForLayout .= $element->toString();
		}

		if (!empty(self::$assetsTop["css"]) || !empty(self::$assets["css"])) {
			if (CACHE_CSS) {
				$cacheFilename = md5(serialize(self::$assetsTop["css"]) . ":" . serialize(self::$assets["css"])) . "_" . URL_SALT . ".css";
				$file = CCSS_DIR . DS . $cacheFilename;

				if (!file_exists($file)) {
					$code = self::getMergedCode("css");

					if (COMPRESS_CSS) {
						$code = self::compress($code, "css");
					}

					file_put_contents($file, $code);
				}

				$element = new Element("link", array(
					"href" => CCSS_URL . "/$cacheFilename",
					"rel" => "stylesheet",
					"type" => "text/css"
				));
			} else {
				$code = self::getMergedCode("css");

				if (COMPRESS_CSS) {
					$code = self::compress($code, "css");
				}

				// Replace relative paths like `url("../bar.png")` with relative-absolute ones like `url("/foo/bar.png")`
				$code = preg_replace('#(url(?:\s*)\((?:|\'|"))\.\./#i', "$1" . BASE_URL . "/", $code);

				$element = new Element("style", array("type" => "text/css"), $code);
			}

			$cssForLayout .= $element->toString();
		}

		if (!Config::read("debug")) {
			Cache::store($cacheKey, $cssForLayout, 0, "apcOnly");
		}

		return $cssForLayout;
	}

	/**
	 * Returns HTML code inlining JS code and/or linking to JS files (depending on JS cache setting).
	 * @return string <script> tags
	 */
	public static function jsForLayout() {
		if (!Config::read("debug")) {
			$cacheKey = "assetHelper:jsForLayout:" . serialize(self::$standaloneAssets["js"]) . ":" . serialize(self::$assetsTop["js"]) . ":" . serialize(self::$assets["js"]);
			$jsForLayout = Cache::fetch($cacheKey, "apcOnly");

			if ($jsForLayout !== false) {
				return $jsForLayout;
			}
		}

		$jsForLayout = "";

		foreach (self::$standaloneAssets["js"] as $filename) {
			$element = new Element("script", array(
				"src" => BASE_URL . "/js/$filename",
				"type" => "text/javascript"
			));

			$jsForLayout .= $element->toString();
		}

		if (!empty(self::$assetsTop["js"]) || !empty(self::$assets["js"])) {
			if (CACHE_JS) {
				$cacheFilename = md5(serialize(self::$assetsTop["js"]) . ":" . serialize(self::$assets["js"])) . "_" . URL_SALT . ".js";
				$file = CJS_DIR . DS . $cacheFilename;

				if (!file_exists($file)) {
					$code = self::getMergedCode("js");

					if (COMPRESS_JS) {
						$code = self::compress($code, "js");
					}

					file_put_contents($file, $code);
				}

				$element = new Element("script", array(
					"src" => CJS_URL . "/$cacheFilename",
					"type" => "text/javascript"
				));
			} else {
				$code = self::getMergedCode("js");

				if (COMPRESS_JS) {
					$code = self::compress($code, "js");
				}

				$element = new Element("script", array("type" => "text/javascript"), "//<![CDATA[\n$code\n//]]>");
			}

			$jsForLayout .= $element->toString();
		}

		if (!Config::read("debug")) {
			Cache::store($cacheKey, $jsForLayout, 0, "apcOnly");
		}

		return $jsForLayout;
	}

	protected static function getMergedCode($type) {
		$mergedCode = "";

		foreach (self::$assetsTop[$type] as $filename) {
			$filepath = WEBROOT_DIR . DS . $type . DS . $filename;

			if (is_file($filepath)) {
				$mergedCode .= file_get_contents($filepath);
			}
		}

		foreach (self::$assets[$type] as $filename) {
			$filepath = WEBROOT_DIR . DS . $type . DS . $filename;

			if (is_file($filepath)) {
				$mergedCode .= file_get_contents($filepath);
			}
		}

		return $mergedCode;
	}

	/**
	 * Compresses CSS and JS code.
	 * Warning: Needs a looong time. Cache the result! Avoid this method at all costs.
	 * @param string $code
	 * @param string $type (either "css" or "js")
	 * @return string Compressed code
	 */
	protected static function compress($code, $type) {
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

			$errors = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			proc_close($process);

			if (empty($errors)) {
				return $compressedCode;
			} else {
				Log::write("Compressing $type failed:\n" . $errors);
			}
		}

		return $code;
	}
}
?>