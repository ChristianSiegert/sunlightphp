<?php
class Inflector {
	/**
	 * Singularizes words by looking them up in Config::config["inflections"].
	 *
	 * @param string $word Word to singularize
	 * @return string Singularized word
	 */
	public static function singularize($word) {
		$inflections = Config::read("inflections");
		return $inflections[$word];
	}
}
?>