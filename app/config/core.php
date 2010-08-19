<?php
/*
 * Use Config::write() to store things that can change. For everything that does
 * not change, define constants.
 */

// 0: Disable debugging, 1: Enable debugging
Config::write("debug", 1);

// Used to look up what model belongs to what controller. Everytime you add a
// controller that makes use of its model, you must add this model here, i.e.
// "users" => "user".
Config::write("inflections", array());

// Connect URLs to a specific controller and/or action.
Router::connect("/", array("controller" => "pages", "action" => "home"));

// Database settings
define("DATABASE_HOST", "http://localhost:5984");
define("DATABASE_NAME", "database-name");

// Used to create unique URLs for CSS and JS files. Always change this salt when
// deploying new or changed CSS or JS code or else browsers will keep using the
// old cached versions.
define("URL_SALT", 201006280);

// Compress assets using the YUI Compressor (best compression, extremely slow).
// Only use in combination with caching assets (see below).
define("COMPRESS_CSS", false);
define("COMPRESS_JS", false);

// Cache assets in files to speed up app. Once cached, the files will never be
// regenerated unless they are deleted or URL_SALT is changed. If assets are not
// cached, they will be included inline in the document (which prevents browsers
// from caching them).
define("CACHE_CSS", false);
define("CACHE_JS", false);

// Enable caching to speed up this app. Disable if extensions are not installed.
define("APC_IS_ENABLED", false);
define("MEMCACHE_IS_ENABLED", false);

// Set unique prefix so this app's cache objects are distinguishable from cache
// objects of other SunlightPHP apps. Maximum prefix length is 215 characters.
define("CACHE_KEY_PREFIX", md5(ROOT_DIR));

// Name of the session cookie. Keep it short.
define("SESSION_NAME", "app-name");

// Maximum session lifetime in seconds
define("SESSION_MAX_LIFETIME", 1209600);
?>
