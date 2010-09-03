<?php
class Cache {
	private static $memcache = null;

	public static $readCount = 0;
	public static $writeCount = 0;

	private static function readyMemcache() {
		if (self::$memcache === null) {
			self::$memcache = new Memcache();
			self::$memcache->connect("localhost", 11211);
		}
	}

	public static function clear() {
		if (APC_IS_ENABLED) {
			apc_clear_cache("user");
		}

		if (MEMCACHE_IS_ENABLED) {
			self::readyMemcache();
			self::$memcache->flush();
		}
	}

	public static function invalidate($key) {
		$key = CACHE_KEY_PREFIX . sha1($key);

		if (APC_IS_ENABLED) {
			apc_delete($key);
		}

		if (MEMCACHE_IS_ENABLED) {
			self::readyMemcache();
			self::$memcache->delete($key);
		}
	}

	public static function store($key, $value, $ttl = 0, $cacheType = null) {
		$key = CACHE_KEY_PREFIX . sha1($key);

		if (APC_IS_ENABLED) {
			apc_store($key, $value, $ttl);
		}

		if ($cacheType !== "apcOnly" && MEMCACHE_IS_ENABLED) {
			self::readyMemcache();
			self::$memcache->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
		}

		self::$writeCount++;
	}

	public static function fetch($key, $cacheType = null) {
		$key = CACHE_KEY_PREFIX . sha1($key);
		$value = false;

		if (APC_IS_ENABLED) {
			$value = apc_fetch($key);
		}

		if ($value === false && $cacheType !== "apcOnly" && MEMCACHE_IS_ENABLED) {
			self::readyMemcache();
			$value = self::$memcache->get($key, MEMCACHE_COMPRESSED);
		}

		self::$readCount++;
		return $value;
	}
}
?>