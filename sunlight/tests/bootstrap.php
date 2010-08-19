<?php
mb_internal_encoding("UTF-8");

ini_set("display_errors", true);

define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("CORE_DIR", ROOT_DIR . DS . "sunlight");
?>