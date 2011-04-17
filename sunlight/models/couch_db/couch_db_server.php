<?php
namespace Models\CouchDb;

use \Exception as Exception;
use Libraries\HttpRequest as HttpRequest;

class CouchDbServer extends CouchDb {
	public function compactDatabase() {
		$this->requireDatabase();

		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/_compact");
		$request->setMethod("post");
		$request->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$request->send();

		if ($request->status !== 202) {
			$request->response = json_decode($request->response);
			throw new Exception(self::describeError($request->response));
		}
	}

	public function compactViewsByDesign($designName) {
		$this->requireDatabase();

		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/_compact/" . rawurlencode($designName));
		$request->setMethod("post");
		$request->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$request->send();

		if ($request->status !== 202) {
			$request->response = json_decode($request->response);
			throw new Exception(self::describeError($request->response));
		}
	}

	public function cleanUpViews() {
		$request = new HttpRequest();
		$request->setUrl($this->databaseHost . "/" . rawurlencode($this->databaseName) . "/_view_cleanup");
		$request->setMethod("post");
		$request->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$request->send();

		if ($request->status !== 202) {
			$request->response = json_decode($request->response);
			throw new Exception(self::describeError($request->response));
		}
	}
}
?>