<?php
use Controllers\Components\Email as Email;

require CORE_DIR . DS . "controllers" . DS . "components" . DS . "email.php";

class EmailComponentDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider isValidAddressDataProvider
	 */
	public function testIsValidAddress($address, $expected) {
		$email = new Email();
		$result = $email->isValidAddress($address);
		$this->assertSame($expected, $result);
	}

	public function isValidAddressDataProvider() {
		return array(
			array("ben@example.com", true),
			array("Ben <ben@example.com>", true),
			array("! # $ % & ' * + - / = ? ^ _ ` { | } ~", "Tom &amp; Jerry"),
		);
	}
}
?>