<?php
use Models\CouchDb\CouchDbDocument as CouchDbDocument;

require CORE_DIR . DS . "models" . DS . "couch_db" . DS . "couch_db.php";
require CORE_DIR . DS . "models" . DS . "couch_db" . DS . "couch_db_document.php";

class CouchDbDocumentDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * Cloning a document should remove any references to internally used
	 * objects.
	 * @dataProvider __cloneDataProvider
	 */
	public function test__clone($couchDbDocument) {
		$couchDbDocumentClone = clone $couchDbDocument;
		$couchDbDocumentClone->foo = "bar";

		$this->assertSame('{}', (string) $couchDbDocument);
		$this->assertSame('{"foo":"bar"}', (string) $couchDbDocumentClone);
	}

	public function __cloneDataProvider() {
		$couchDbDocument = new CouchDbDocument();
		return array(array($couchDbDocument));
	}

	/**
	 * Accessing a field should not alter the document.
	 * @dataProvider __getDataProvider
	 */
	public function test__get($couchDbDocument, $fieldName, $expectedFieldValue) {
		$couchDbDocumentClone = clone $couchDbDocument;
		$actualFieldValue = $couchDbDocumentClone->$fieldName;

		$this->assertSame($expectedFieldValue, $actualFieldValue);
		$this->assertEquals($couchDbDocumentClone, $couchDbDocument);
	}

	public function __getDataProvider() {
		$dataProvider = array();

		// Access field that does not exist
		$couchDbDocument = new CouchDbDocument("test");
		$fieldName = "foo";
		$expectedFieldValue = null;

		$dataProvider[] = array($couchDbDocument, $fieldName, $expectedFieldValue);

		// Access field that exists
		$couchDbDocument = new CouchDbDocument("test");
		$couchDbDocument->foo = "bar";
		$fieldName = "foo";
		$expectedFieldValue = "bar";

		$dataProvider[] = array($couchDbDocument, $fieldName, $expectedFieldValue);

		return $dataProvider;
	}

	/**
	 * Setting a field should convert a passed associative array to stdClass.
	 * @dataProvider __setDataProvider
	 */
	public function test__set($couchDbDocument, $expectedCouchDbDocument) {
		$expected = print_r($expectedCouchDbDocument, true);
		$actual = print_r($couchDbDocument, true);

		$this->assertEquals($expected, $actual);
	}

	public function __setDataProvider() {
		$dataProvider = array();

		// Setting fields that are sometimes associative arrays
		$couchDbDocument = new CouchDbDocument("test");
		$couchDbDocument->foo_1 = "bar_1";
		$couchDbDocument->foo_2 = array("bar_2_a");
		$couchDbDocument->foo_3 = array("foo_3_1" => "bar_3_1");
		$couchDbDocument->foo_4 = array("bar_2_a", "foo_3_1" => "bar_3_1");

		// Associative arrays should have been converted to stdClass objects
		$expectedCouchDbDocument = new CouchDbDocument("test");
		$expectedCouchDbDocument->foo_1 = "bar_1";
		$expectedCouchDbDocument->foo_2 = array("bar_2_a");
		$expectedCouchDbDocument->foo_3 = new stdClass();
		$expectedCouchDbDocument->foo_3->foo_3_1 = "bar_3_1";
		$expectedCouchDbDocument->foo_4 = new stdClass();
		$expectedCouchDbDocument->foo_4->{0} ="bar_2_a";
		$expectedCouchDbDocument->foo_4->foo_3_1 = "bar_3_1";

		$dataProvider[] = array($couchDbDocument, $expectedCouchDbDocument);

		return $dataProvider;
	}

	/**
	 * Merging an array with the document should happen recursively.
	 * @dataProvider mergeDataProvider
	 */
	public function testMerge($couchDbDocument, $thing, $expected) {
		$couchDbDocument->merge($thing);
		$this->assertEquals($expected, $couchDbDocument);
	}

	public function mergeDataProvider() {
		$dataProvider = array();

		$couchDbDocument = new CouchDbDocument("test");
		$couchDbDocument->foo_1 = "bar_1";
		$couchDbDocument->foo_2 = "bar_2";
		$couchDbDocument->foo_3 = array("bar_3");
		$couchDbDocument->foo_4 = new stdClass();
		$couchDbDocument->foo_4->foo_4_1 = "bar_4_1";
		$couchDbDocument->foo_5 = array("foo_5_1" => "bar_5_1");

		$thing = new stdClass();
		$thing->foo_2 = "bar_2_b";
		$thing->foo_3 = array("bar_3_b");
		$thing->foo_4 = new stdClass();
		$thing->foo_4->foo_4_2 = "bar_4_2";
		$thing->foo_5 = array("foo_5_2" => "bar_5_2");

		$expectedCouchDbDocument = new CouchDbDocument("test");
		$expectedCouchDbDocument->foo_1 = "bar_1";
		$expectedCouchDbDocument->foo_2 = "bar_2_b";
		$expectedCouchDbDocument->foo_3 = array("bar_3_b");
		$expectedCouchDbDocument->foo_4 = new stdClass();
		$expectedCouchDbDocument->foo_4->foo_4_1 = "bar_4_1";
		$expectedCouchDbDocument->foo_4->foo_4_2 = "bar_4_2";
		$expectedCouchDbDocument->foo_5 = new stdClass();
		$expectedCouchDbDocument->foo_5->foo_5_1 = "bar_5_1";
		$expectedCouchDbDocument->foo_5->foo_5_2 = "bar_5_2";

		$dataProvider[] = array($couchDbDocument, $thing, $expectedCouchDbDocument);

		return $dataProvider;
	}
}
?>