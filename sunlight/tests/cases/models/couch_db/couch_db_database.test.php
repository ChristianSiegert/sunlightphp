<?php
use Models\CouchDb\CouchDbDatabase;

require CORE_DIR . DS . "models" . DS . "couch_db" . DS . "couch_db.php";
require CORE_DIR . DS . "models" . DS . "couch_db" . DS . "couch_db_database.php";

class CouchDbDocumentDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider isDatabaseNameDataProvider
	 */
	public function testIsDatabaseName($databaseName, $expectedResult) {
		$actualResult = CouchDbDatabase::isDatabaseName($databaseName);
		$this->assertSame($expectedResult, $actualResult);
	}

	public function isDatabaseNameDataProvider() {
		return array(
			// Database name does not start with lower-case letter
			array("A", false),
			array("B", false),
			array("C", false),
			array("D", false),
			array("E", false),
			array("F", false),
			array("G", false),
			array("H", false),
			array("I", false),
			array("J", false),
			array("K", false),
			array("L", false),
			array("M", false),
			array("N", false),
			array("O", false),
			array("P", false),
			array("Q", false),
			array("R", false),
			array("S", false),
			array("T", false),
			array("U", false),
			array("V", false),
			array("W", false),
			array("X", false),
			array("Y", false),
			array("Z", false),
			array("1", false),
			array("2", false),
			array("3", false),
			array("4", false),
			array("5", false),
			array("6", false),
			array("7", false),
			array("8", false),
			array("9", false),
			array("0", false),
			array("_", false),
			array("$", false),
			array("(", false),
			array(")", false),
			array("+", false),
			array("-", false),
			array("/", false),

			// Database name starts with lower-case letter
			array("a", true),
			array("b", true),
			array("c", true),
			array("d", true),
			array("e", true),
			array("f", true),
			array("g", true),
			array("h", true),
			array("i", true),
			array("j", true),
			array("k", true),
			array("l", true),
			array("m", true),
			array("n", true),
			array("o", true),
			array("p", true),
			array("q", true),
			array("r", true),
			array("s", true),
			array("t", true),
			array("u", true),
			array("v", true),
			array("w", true),
			array("x", true),
			array("y", true),
			array("z", true),
			array("a1", true),
			array("a2", true),
			array("a3", true),
			array("a4", true),
			array("a5", true),
			array("a6", true),
			array("a7", true),
			array("a8", true),
			array("a9", true),
			array("a0", true),
			array("a_", true),
			array("a$", true),
			array("a(", true),
			array("a)", true),
			array("a+", true),
			array("a-", true),
			array("a/", true),

			// Misc
			array("", false),
			array("abcdefghijklmnopqrstuvwxyz1234567890_$()+-/", true),
			array("ABCDEFGHIJKLMNOPQRSTUVWXYZ", false),
			array("ab", true),
			array("Ab", false),
			array("aB", false),
			array("abc", true),
			array("Abc", false),
			array("aBc", false),
		);
	}
}
?>