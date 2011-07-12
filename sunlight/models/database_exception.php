<?php
namespace Models;

class DatabaseException extends \Exception {
	const CREATING_DATABASE_FAILED = 1;
	const HTTP_REQUEST_FAILED = 2;
	const INVALID_DATABASE_NAME = 3;
}
?>