<?php
use Controllers\Components\Email as Email;

require CORE_DIR . DS . "controllers" . DS . "components" . DS . "email.php";

class EmailComponentDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider isValidAddressDataProvider
	 */
	/*
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
	*/

	/**
	 * @dataProvider formatAddressDataProvider
	 */
	public function testFormatAddress($eMailAddress, $displayName, $expectedResult) {
		$actualResult = Email::formatAddress($eMailAddress, $displayName);
		$this->assertSame($expectedResult, $actualResult);
	}

	public function formatAddressDataProvider() {
		return array(
			array("user@example.com", "", "user@example.com"),
			array("user@example.com", "User", "User <user@example.com>"),
		);
	}
}
?>