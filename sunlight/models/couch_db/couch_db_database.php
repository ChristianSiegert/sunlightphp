<?php
namespace Models\CouchDb;

use \Exception;
use Libraries\HttpRequest;
use Models\DatabaseException;

class CouchDbDatabase extends CouchDb {
	/**
	 * Creates a database.
	 * @param string $databaseHost
	 * @param string $databaseName
	 * @throws \Models\DatabaseException
	 */
	public static function create($databaseHost, $databaseName) {
		if (!self::isDatabaseName($databaseName)) {
			throw new DatabaseException("Database name '$databaseName' is not valid. It must begin with a lower-case letter, optionally followed by: a-z, 0-9, _$()+-/", DatabaseException::INVALID_DATABASE_NAME);
		}

		try {
			$request = new HttpRequest();
			$request->setMethod("put");
			$request->setUrl($databaseHost . "/" . rawurlencode($databaseName));
			$request->send();
		} catch (Exception $exception) {
			throw new DatabaseException("HTTP request failed.", DatabaseException::HTTP_REQUEST_FAILED, $exception);
		}

		if ($request->status !== 201) {
			throw new DatabaseException($request->response, DatabaseException::CREATING_DATABASE_FAILED);
		}
	}

	/**
	 * Checks if a database name is valid according to CouchDB's specification
	 * (as of CouchDB 1.0.1).
	 * @param string $databaseName
	 * return boolean
	 */
	public static function isDatabaseName($databaseName) {
		return preg_match('#^[a-z][a-z0-9_$()/+-]*$#', $databaseName) === 1;
	}
}
?>