<?php
use Libraries\Uuid;

require CORE_DIR . DS . "libraries" . DS . "uuid.php";

class StringDataTest extends PHPUnit_Framework_TestCase {
	public function testV4() {
		$this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', Uuid::v4());
	}

	/**
	 * @dataProvider v5DataProvider
	 */
	public function testV5($namespace, $name, $expected) {
		$result = Uuid::v5($namespace, $name);
		$this->assertSame($expected, $result);
	}

	public function v5DataProvider() {
		return array(
			array("e983babc-80e8-4d6f-b934-dd0b744d8045", "foo", "515b8568-4956-51f1-98ec-552afb7c34a4"),
			array("e983babc-80e8-4d6f-b934-dd0b744d8045", "bar", "25573929-68ac-5b11-885f-12353ca60caf"),
		);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testV5Exception() {
		Uuid::v5("foo", "bar");
	}

	/**
	 * @dataProvider isValidDataProvider
	 */
	public function testIsValid($input, $expected) {
		$result = Uuid::isValid($input);
		$this->assertSame($result, $expected);
	}

	public function isValidDataProvider() {
		return array(
			array("00000000-0000-0000-0000-000000000000", true),
			array("e983babc-80e8-4d6f-b934-dd0b744d8045", true),
			array("ffffffff-ffff-ffff-ffff-ffffffffffff", true),
			array("g0000000-0000-0000-0000-000000000000", false),
			array("foo", false),
		);
	}
}
?>