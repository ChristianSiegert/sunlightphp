<?php
namespace Libraries;

use \InvalidArgumentException;

class Uuid {
	const EXCEPTION_INVALID_NAMESPACE = 0;

	/**
	 * Returns a random UUID (version 4).
	 * Taken from http://www.php.net/manual/en/function.uniqid.php#94959
	 * @return string UUID
	 */
	public static function v4() {
		return sprintf(
			"%04x%04x-%04x-%04x-%04x-%04x%04x%04x",

			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * Returns a UUID (version 5).
	 * Adapted from http://www.php.net/manual/en/function.uniqid.php#94959
	 * @param string $namespace UUID
	 * @param string $name Any string
	 * @throws InvalidArgumentException if namespace is not valid UUID
	 */
	public static function v5($namespace, $name) {
		if (!self::isValid($namespace)) {
			throw new InvalidArgumentException("The namespace must be a valid UUID.", self::EXCEPTION_INVALID_NAMESPACE);
		}

		// Get hexadecimal components of namespace
		$namespaceHex = str_replace(array("-", "{", "}"), "", $namespace);

		// Binary Value
		$namespaceString = "";

		// Convert Namespace UUID to bits
		for ($i = 0; $i < strlen($namespaceHex); $i += 2) {
			$namespaceString .= chr(hexdec($namespaceHex[$i] . $namespaceHex[$i + 1]));
		}

		// Calculate hash value
		$hash = sha1($namespaceString . $name);

		return sprintf(
			"%08s-%04s-%04x-%04x-%12s",

			// 32 bits for "time_low"
			substr($hash, 0, 8),

			// 16 bits for "time_mid"
			substr($hash, 8, 4),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 5
			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

			// 48 bits for "node"
			substr($hash, 20, 12)
		);
	}

	/**
	 * Checks if the provided UUID is valid.
	 * @param string $uuid UUID
	 * @return boolean True if valid, otherwise false
	 */
	public static function isValid($uuid) {
		return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
	}
}
?>