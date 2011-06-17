<?php
require CORE_DIR . DS . "models" . DS . "couch_db" . DS . "couch_db.php";
require CORE_DIR . DS . "models" . DS . "couch_db" . DS . "couch_db_document.php";
require CORE_DIR . DS . "models" . DS . "document.php";

// Our fake database info
define("DATABASE_HOST", "empty");
define("DATABASE_NAME", "empty");

class TestDocument extends Models\Document {
	public static function checkAgainstWhitelist($document, $whitelist) {
		return parent::checkAgainstWhitelist($document, $whitelist);
	}
}


class DocumentDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider checkWhitelistDataProvider
	 */
	public function testCheckWhitelist($document, $whitelist, $expectedErrors) {
		$actualErrors = TestDocument::checkAgainstWhitelist($document, $whitelist);
		$this->assertSame($expectedErrors, $actualErrors);
	}

	public function checkWhitelistDataProvider() {
		$dataProvider = array();

		/*
		// All fields are whitelisted
		$case1 = array(
			"document" => array(),
			"fieldList" => array(),
		);

		// All fields are whitelisted
		$case2 = array(
			"document" => array(
				"foo" => "blah"
			),
			"fieldList" => array(
				"foo"
			),
		);

		// All fields are whitelisted
		$case3 = array(
			"document" => array(
				"foo" => array(
					"bar" => "blah"
				)
			),
			"fieldList" => array(
				"foo" => array(
					"bar"
				)
			),
		);

		// All fields are whitelisted
		$case4 = array(
			"document" => array(
				"foo" => array(
					"bar" => array(
						"moo" => "blah"
					)
				)
			),
			"fieldList" => array(
				"foo" => array(
					"bar" => array(
						"moo"
					)
				)
			),
		);

		// All fields are whitelisted
		$case5 = array(
			"document" => array(
				"foo" => array(
					"bar" => array(
						"moo" => array(
							"blubber" => "blah"
						)
					)
				)
			),
			"fieldList" => array(
				"foo" => array(
					"bar" => array(
						"moo" => array(
							"blubber"
						)
					)
				)
			),
		);

		// All fields are whitelisted
		$case6 = array(
			"document" => array(
				"added" => "blah",
				"address" => array(
					"city" => "blah",
					"description" => array(
						"html" => array(
							"strict" => "blah",
							"transitional" => "blah",
						),
						"plain" => "blah",
					),
				),
				"title" => "blah",
				"user_id" => "blah",
			),
			"fieldList" => array(
				"added",
				"address" => array(
					"city",
					"description" => array(
						"html" => array(
							"strict",
							"transitional",
						),
						"plain",
					),
					"house_number",
					"street",
					"zip_code",
				),
				"title",
				"user_id",
			),
		);

		// Not all fields are whitelisted
		$case7 = array(
			"document" => array(
				"foo" => "blah"
			),
			"fieldList" => array(),
		);

		// Not all fields are whitelisted
		$case8 = array(
			"document" => array(
				"foo" => "blah"
			),
			"fieldList" => array(
				"bar"
			),
		);

		// Not all fields are whitelisted
		$case9 = array(
			"document" => array(
				"foo" => array(
					"bar" => "blah"
				)
			),
			"fieldList" => array(
				"foo"
			),
		);

		// Not all fields are whitelisted
		$case10 = array(
			"document" => array(
				"foo" => array(
					"bar" => "blah"
				)
			),
			"fieldList" => array(
				"foo" => array(
					"asd"
				)
			),
		);

		// Not all fields are whitelisted
		$case11 = array(
			"document" => array(
				"foo" => array(
					"bar" => array(
						"moo" => "blah"
					)
				)
			),
			"fieldList" => array(
				"foo" => array(
					"bar"
				)
			),
		);

		// Not all fields are whitelisted
		$case12 = array(
			"document" => array(
				"foo" => array(
					"bar" => array(
						"moo" => "blah"
					)
				)
			),
			"fieldList" => array(
				"foo" => array(
					"bar" => array(
						"asd"
					)
				)
			),
		);
		*/

		// 0 document fields, 0 corresponding fields whitelisted, 0 non-corresponding fields whitelisted
		$document = new stdClass();
		$whitelist = array();
		$expected = true;

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document field, 0 corresponding fields whitelisted, 0 non-corresponding fields whitelisted
		$document = new stdClass();
		$document->foo_1 = "bar_1";

		$whitelist = array();

		$expected = "foo_1";

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document fields, 0 corresponding fields whitelisted, 1 non-corresponding field whitelisted
		$document = new stdClass();
		$document->foo_1 = "bar_1";

		$whitelist = array(
			"foo_2"
		);

		$expected = "foo_1";

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document fields, 1 corresponding field whitelisted, 0 non-corresponding fields whitelisted
		$document = new stdClass();
		$document->foo_1 = "bar_1";

		$whitelist = array(
			"foo_1"
		);

		$expected = true;

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document fields, 1 corresponding field whitelisted, 1 non-corresponding field whitelisted
		$document = new stdClass();
		$document->foo_1 = "bar_1";

		$whitelist = array(
			"foo_1",
			"foo_2"
		);

		$expected = true;

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document field with 1 nested field, 0 corresponding field whitelisted, 0 non-corresponding field whitelisted
		$document = new stdClass();
		$document->foo_1 = new stdClass();
		$document->foo_1->foo_1_1 = "bar_1_1";

		$whitelist = array();

		$expected = "foo_1";

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document field with 1 nested field, 0 corresponding field whitelisted, 1 non-corresponding field whitelisted
		$document = new stdClass();
		$document->foo_1 = new stdClass();
		$document->foo_1->foo_1_1 = "bar_1_1";

		$whitelist = array(
			"foo_1" => array(
				"foo_1_2"
			)
		);

		$expected = "foo_1_1";

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document field with 1 nested field, 1 corresponding field whitelisted, 0 non-corresponding field whitelisted
		$document = new stdClass();
		$document->foo_1 = new stdClass();
		$document->foo_1->foo_1_1 = "bar_1_1";

		$whitelist = array(
			"foo_1" => array(
				"foo_1_1"
			)
		);

		$expected = true;

		$dataProvider[] = array($document, $whitelist, $expected);

		// 1 document field with 1 nested field, 1 corresponding field whitelisted, 1 non-corresponding field whitelisted
		$document = new stdClass();
		$document->foo_1 = new stdClass();
		$document->foo_1->foo_1_1 = "bar_1_1";

		$whitelist = array(
			"foo_1" => array(
				"foo_1_1",
				"foo_1_2"
			)
		);

		$expected = true;

		$dataProvider[] = array($document, $whitelist, $expected);

		// Not all fields are whitelisted
		$document = new stdClass();

		// Nesting levels: 0
		$document->foo_1 = null;
		$document->foo_2 = false;
		$document->foo_3 = true;
		$document->foo_4 = 0;
		$document->foo_5 = 1;
		$document->foo_6 = 2.3;
		$document->foo_7 = "";
		$document->foo_8 = "blah";
		$document->foo_9 = array();

		// Nesting levels: 1 (indexed array)
		$document->foo_10 = array(null);
		$document->foo_11 = array(false);
		$document->foo_12 = array(true);
		$document->foo_13 = array(0);
		$document->foo_14 = array(1);
		$document->foo_15 = array(2.3);
		$document->foo_16 = array("");
		$document->foo_17 = array("blah");
		$document->foo_18 = array(array());

		// Nesting levels: 1 (associative array)
		$document->foo_19 = array("foo_19_1" => null);
		$document->foo_20 = array("foo_20_1" => false);
		$document->foo_21 = array("foo_21_1" => true);
		$document->foo_22 = array("foo_22_1" => 0);
		$document->foo_23 = array("foo_23_1" => 1);
		$document->foo_24 = array("foo_24_1" => 2.3);
		$document->foo_25 = array("foo_25_1" => "");
		$document->foo_26 = array("foo_26_1" => "blah");
		$document->foo_27 = array("foo_27_1" => array());

		// Nesting levels: 2 (indexed array, indexed array)
		$document->foo_28 = array(array(null));
		$document->foo_29 = array(array(false));
		$document->foo_30 = array(array(true));
		$document->foo_31 = array(array(0));
		$document->foo_32 = array(array(1));
		$document->foo_33 = array(array(2.3));
		$document->foo_34 = array(array(""));
		$document->foo_35 = array(array("blah"));
		$document->foo_36 = array(array(array()));

		// Nesting levels: 2 (indexed array, associative array)
		$document->foo_37 = array(array("foo_37_1_1" => null));
		$document->foo_38 = array(array("foo_38_1_1" => false));
		$document->foo_39 = array(array("foo_39_1_1" => true));
		$document->foo_40 = array(array("foo_40_1_1" => 0));
		$document->foo_41 = array(array("foo_41_1_1" => 1));
		$document->foo_42 = array(array("foo_42_1_1" => 2.3));
		$document->foo_43 = array(array("foo_43_1_1" => ""));
		$document->foo_44 = array(array("foo_44_1_1" => "blah"));
		$document->foo_45 = array(array("foo_45_1_1" => array()));

		// Nesting levels: 2 (associative array, indexed array)
		$document->foo_46 = array("foo_46_1" => array(null));
		$document->foo_47 = array("foo_47_1" => array(false));
		$document->foo_48 = array("foo_48_1" => array(true));
		$document->foo_49 = array("foo_49_1" => array(0));
		$document->foo_50 = array("foo_50_1" => array(1));
		$document->foo_51 = array("foo_51_1" => array(2.3));
		$document->foo_52 = array("foo_52_1" => array(""));
		$document->foo_53 = array("foo_53_1" => array("blah"));
		$document->foo_54 = array("foo_54_1" => array(array()));

		// Nesting levels: 2 (associative array, associative array)
		$document->foo_55 = array("foo_55_1" => array("foo_55_1_1" => null));
		$document->foo_56 = array("foo_56_1" => array("foo_56_1_1" => false));
		$document->foo_57 = array("foo_57_1" => array("foo_57_1_1" => true));
		$document->foo_58 = array("foo_58_1" => array("foo_58_1_1" => 0));
		$document->foo_59 = array("foo_59_1" => array("foo_59_1_1" => 1));
		$document->foo_60 = array("foo_60_1" => array("foo_60_1_1" => 2.3));
		$document->foo_61 = array("foo_61_1" => array("foo_61_1_1" => ""));
		$document->foo_62 = array("foo_62_1" => array("foo_62_1_1" => "blah"));
		$document->foo_63 = array("foo_63_1" => array("foo_63_1_1" => array()));

		$whitelist = array(
			"foo_1",
			"foo_2",
			"foo_3",
			"foo_4",
			"foo_5",
			"foo_6",
			"foo_7",
			"foo_8",
			"foo_9",

			"foo_10",
			"foo_11",
			"foo_12",
			"foo_13",
			"foo_14",
			"foo_15",
			"foo_16",
			"foo_17",
			"foo_18",

			"foo_19" => array("foo_19_1"),
			"foo_20" => array("foo_20_1"),
			"foo_21" => array("foo_21_1"),
			"foo_22" => array("foo_22_1"),
			"foo_23" => array("foo_23_1"),
			"foo_24" => array("foo_24_1"),
			"foo_25" => array("foo_25_1"),
			"foo_26" => array("foo_26_1"),
			"foo_27" => array("foo_27_1"),

			"foo_28",
			"foo_29",
			"foo_30",
			"foo_31",
			"foo_32",
			"foo_33",
			"foo_34",
			"foo_35",
			"foo_36",

			"foo_37",
			"foo_38",
			"foo_39",
			"foo_40",
			"foo_41",
			"foo_42",
			"foo_43",
			"foo_44",
			"foo_45",

			"foo_46" => array("foo_46_1"),
			"foo_47" => array("foo_47_1"),
			"foo_48" => array("foo_48_1"),
			"foo_49" => array("foo_49_1"),
			"foo_50" => array("foo_50_1"),
			"foo_51" => array("foo_51_1"),
			"foo_52" => array("foo_52_1"),
			"foo_53" => array("foo_53_1"),
			"foo_54" => array("foo_54_1"),

			"foo_55" => array("foo_55_1" => array("foo_55_1_1")),
			"foo_56" => array("foo_56_1" => array("foo_56_1_1")),
			"foo_57" => array("foo_57_1" => array("foo_57_1_1")),
			"foo_58" => array("foo_58_1" => array("foo_58_1_1")),
			"foo_59" => array("foo_59_1" => array("foo_59_1_1")),
			"foo_60" => array("foo_60_1" => array("foo_60_1_1")),
			"foo_61" => array("foo_61_1" => array("foo_61_1_1")),
			"foo_62" => array("foo_62_1" => array("foo_62_1_1")),
			"foo_63" => array("foo_63_1" => array("foo_63_1_1")),
		);

		$expected = true;

		$dataProvider[] = array($document, $whitelist, $expected);

		return $dataProvider;
	}

	/**
	 * @dataProvider validateDataProvider
	 */
	public function testValidate($document, $rules, $expected) {
		$result = $document->validate($document, $rules);
		$this->assertSame($expected, $result);
	}

	public function validateDataProvider() {
		$dataProvider = array();

		// 0 fields, 0 rules, 0 expected validation errors
		$document = new Models\Document("test");
		$rules = array();
		$expectedValidationErrors = array();

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);


		// 0 fields, 1 rule, 1 expected validation error
		$document = new Models\Document("test");

		$rules = array(
			"foo" => array(
				"rule" => "isTimestamp"
			)
		);

		$expectedValidationErrors = array(
			"foo" => array(
				array(
					"message" => "Value for field 'foo' is not valid.",
					"value" => "",
				)
			)
		);

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);


		// 1 field, 1 rule, 0 expected validation errors
		$document = new Models\Document("test");
		$document->foo = 3;

		$rules = array(
			"foo" => array(
				"rule" => "isNumeric"
			)
		);

		$expectedValidationErrors = array();

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);


		// 1 field, 1 rule, 1 expected validation error
		$document = new Models\Document("test");
		$document->foo = "3";

		$rules = array(
			"foo" => array(
				"rule" => "isNumeric"
			)
		);

		$expectedValidationErrors = array(
			"foo" => array(
				array(
					"message" => "Value for field 'foo' is not valid.",
					"value" => "3",
				)
			)
		);

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);


		// 0 fields, 1 nested rule, 1 expected validation error
		$document = new Models\Document("test");

		$rules = array(
			"foo" => array(
				"contains" => array(
					"bar" => array(
						"rule" => "isTimestamp"
					)
				)
			)
		);

		$expectedValidationErrors = array(
			"foo" => array(
				"bar" => array(
					array(
						"message" => "Value for field 'bar' is not valid.",
						"value" => "",
					)
				)
			)
		);

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);

		// Stress test
		$document = new Models\Document("test");
		$document->foo_a = "bar_a";
		$document->foo_b = "bar_b";
		$document->foo_c = "bar_c";
		$document->foo_d = 3;
		$document->foo_e = 3;
		$document->foo_f = 3;
		$document->foo_g = new stdClass();
		$document->foo_g->foo_g_1 = "bar_g_1";
		$document->foo_g->foo_g_2 = "bar_g_2";
		$document->foo_g->foo_g_3 = "bar_g_3";
		$document->foo_g->foo_g_4 = 3;
		$document->foo_g->foo_g_5 = 3;
		$document->foo_g->foo_g_6 = 3;
		$document->foo_g->foo_g_7 = new stdClass();
		$document->foo_g->foo_g_7->foo_g_7_1 = "bar_g_7_1";
		$document->foo_g->foo_g_7->foo_g_7_2 = "bar_g_7_2";
		$document->foo_g->foo_g_7->foo_g_7_3 = "bar_g_7_3";
		$document->foo_g->foo_g_7->foo_g_7_4 = 3;
		$document->foo_g->foo_g_7->foo_g_7_5 = 3;
		$document->foo_g->foo_g_7->foo_g_7_6 = 3;
		$document->foo_l = array(
			"foo_l_1" => "bar_l_1",
			"foo_l_2" => "bar_l_2",
			"foo_l_3" => "bar_l_3",
			"foo_l_4" => 3,
			"foo_l_5" => 3,
			"foo_l_6" => 3,
			"foo_l_7" => array(
				"foo_l_7_1" => "bar_l_7_1",
				"foo_l_7_2" => "bar_l_7_2",
				"foo_l_7_3" => "bar_l_7_3",
				"foo_l_7_4" => 3,
				"foo_l_7_5" => 3,
				"foo_l_7_6" => 3,
			),
		);

		$rules = array(
			"foo_a" => array(
				"rule" => "isNotEmpty"
			),
			"foo_b" => array(
				"rule" => "isNumeric"
			),
			"foo_c" => array(
				"rule" => "isNumeric",
				"message" => "The value you entered must be a number."
			),
			"foo_d" => array(
				"rule" => array("isInRange", 1, 10)
			),
			"foo_e" => array(
				"rule" => array("isInRange", 8, 10)
			),
			"foo_f" => array(
				"rule" => array("isInRange", 8, 10),
				"message" => "The value you entered must be between 8 and 10."
			),
			"foo_g" => array(
				"contains" => array(
					"foo_g_1" => array(
						"rule" => "isNotEmpty"
					),
					"foo_g_2" => array(
						"rule" => "isNumeric"
					),
					"foo_g_3" => array(
						"rule" => "isNumeric",
						"message" => "The value you entered must be a number."
					),
					"foo_g_4" => array(
						"rule" => array("isInRange", 1, 10)
					),
					"foo_g_5" => array(
						"rule" => array("isInRange", 8, 10)
					),
					"foo_g_6" => array(
						"rule" => array("isInRange", 8, 10),
						"message" => "The value you entered must be between 8 and 10."
					),
					"foo_g_7" => array(
						"contains" => array(
							"foo_g_7_1" => array(
								"rule" => "isNotEmpty"
							),
							"foo_g_7_2" => array(
								"rule" => "isNumeric"
							),
							"foo_g_7_3" => array(
								"rule" => "isNumeric",
								"message" => "The value you entered must be a number."
							),
							"foo_g_7_4" => array(
								"rule" => array("isInRange", 1, 10)
							),
							"foo_g_7_5" => array(
								"rule" => array("isInRange", 8, 10)
							),
							"foo_g_7_6" => array(
								"rule" => array("isInRange", 8, 10),
								"message" => "The value you entered must be between 8 and 10."
							),
							"foo_g_7_7" => array(
								"rule" => "isNotEmpty"
							),
							"foo_g_7_8" => array(
								"rule" => "isNotEmpty",
								"message" => "Please enter something."
							),
							"foo_g_7_9" => array(
								"rule" => array("isInRange", 1, 10)
							),
							"foo_g_7_10" => array(
								"rule" => array("isInRange", 1, 10),
								"message" => "The value you entered must be between 1 and 10."
							),
						)
					),
					"foo_g_8" => array(
						"rule" => "isNotEmpty"
					),
					"foo_g_9" => array(
						"rule" => "isNotEmpty",
						"message" => "Please enter something."
					),
					"foo_g_10" => array(
						"rule" => array("isInRange", 1, 10)
					),
					"foo_g_11" => array(
						"rule" => array("isInRange", 1, 10),
						"message" => "The value you entered must be between 1 and 10."
					),
				)
			),
			"foo_h" => array(
				"rule" => "isNotEmpty"
			),
			"foo_i" => array(
				"rule" => "isNotEmpty",
				"message" => "Please enter something."
			),
			"foo_j" => array(
				"rule" => array("isInRange", 1, 10)
			),
			"foo_k" => array(
				"rule" => array("isInRange", 1, 10),
				"message" => "The value you entered must be between 1 and 10."
			),
			"foo_l" => array(
				"contains" => array(
					"foo_l_1" => array(
						"rule" => "isNotEmpty"
					),
					"foo_l_2" => array(
						"rule" => "isNumeric"
					),
					"foo_l_3" => array(
						"rule" => "isNumeric",
						"message" => "The value you entered must be a number."
					),
					"foo_l_4" => array(
						"rule" => array("isInRange", 1, 10)
					),
					"foo_l_5" => array(
						"rule" => array("isInRange", 8, 10)
					),
					"foo_l_6" => array(
						"rule" => array("isInRange", 8, 10),
						"message" => "The value you entered must be between 8 and 10."
					),
					"foo_l_7" => array(
						"contains" => array(
							"foo_l_7_1" => array(
								"rule" => "isNotEmpty"
							),
							"foo_l_7_2" => array(
								"rule" => "isNumeric"
							),
							"foo_l_7_3" => array(
								"rule" => "isNumeric",
								"message" => "The value you entered must be a number."
							),
							"foo_l_7_4" => array(
								"rule" => array("isInRange", 1, 10)
							),
							"foo_l_7_5" => array(
								"rule" => array("isInRange", 8, 10)
							),
							"foo_l_7_6" => array(
								"rule" => array("isInRange", 8, 10),
								"message" => "The value you entered must be between 8 and 10."
							),
							"foo_l_7_7" => array(
								"rule" => "isNotEmpty"
							),
							"foo_l_7_8" => array(
								"rule" => "isNotEmpty",
								"message" => "Please enter something."
							),
							"foo_l_7_9" => array(
								"rule" => array("isInRange", 1, 10)
							),
							"foo_l_7_10" => array(
								"rule" => array("isInRange", 1, 10),
								"message" => "The value you entered must be between 1 and 10."
							),
						)
					),
					"foo_l_8" => array(
						"rule" => "isNotEmpty"
					),
					"foo_l_9" => array(
						"rule" => "isNotEmpty",
						"message" => "Please enter something."
					),
					"foo_l_10" => array(
						"rule" => array("isInRange", 1, 10)
					),
					"foo_l_11" => array(
						"rule" => array("isInRange", 1, 10),
						"message" => "The value you entered must be between 1 and 10."
					),
				)
			),
		);

		$expectedValidationErrors = array(
			"foo_b" => array(
				array(
					"message" => "Value for field 'foo_b' is not valid.",
					"value" => "bar_b"
				)
			),
			"foo_c" => array(
				array(
					"message" => "The value you entered must be a number.",
					"value" => "bar_c"
				)
			),
			"foo_e" => array(
				array(
					"message" => "Value for field 'foo_e' is not valid.",
					"value" => 3
				)
			),
			"foo_f" => array(
				array(
					"message" => "The value you entered must be between 8 and 10.",
					"value" => 3
				)
			),
			"foo_g" => array(
				"foo_g_2" => array(
					array(
						"message" => "Value for field 'foo_g_2' is not valid.",
						"value" => "bar_g_2"
					)
				),
				"foo_g_3" => array(
					array(
						"message" => "The value you entered must be a number.",
						"value" => "bar_g_3"
					)
				),
				"foo_g_5" => array(
					array(
						"message" => "Value for field 'foo_g_5' is not valid.",
						"value" => 3
					)
				),
				"foo_g_6" => array(
					array(
						"message" => "The value you entered must be between 8 and 10.",
						"value" => 3
					)
				),
				"foo_g_7" => array(
					"foo_g_7_2" => array(
						array(
							"message" => "Value for field 'foo_g_7_2' is not valid.",
							"value" => "bar_g_7_2"
						)
					),
					"foo_g_7_3" => array(
						array(
							"message" => "The value you entered must be a number.",
							"value" => "bar_g_7_3"
						)
					),
					"foo_g_7_5" => array(
						array(
							"message" => "Value for field 'foo_g_7_5' is not valid.",
							"value" => 3
						)
					),
					"foo_g_7_6" => array(
						array(
							"message" => "The value you entered must be between 8 and 10.",
							"value" => 3
						)
					),
					"foo_g_7_7" => array(
						array(
							"message" => "Value for field 'foo_g_7_7' is not valid.",
							"value" => ""
						)
					),
					"foo_g_7_8" => array(
						array(
							"message" => "Please enter something.",
							"value" => ""
						)
					),
					"foo_g_7_9" => array(
						array(
							"message" => "Value for field 'foo_g_7_9' is not valid.",
							"value" => ""
						)
					),
					"foo_g_7_10" => array(
						array(
							"message" => "The value you entered must be between 1 and 10.",
							"value" => ""
						)
					)
				),
				"foo_g_8" => array(
					array(
						"message" => "Value for field 'foo_g_8' is not valid.",
						"value" => ""
					)
				),
				"foo_g_9" => array(
					array(
						"message" => "Please enter something.",
						"value" => ""
					)
				),
				"foo_g_10" => array(
					array(
						"message" => "Value for field 'foo_g_10' is not valid.",
						"value" => ""
					)
				),
				"foo_g_11" => array(
					array(
						"message" => "The value you entered must be between 1 and 10.",
						"value" => ""
					)
				)
			),
			"foo_h" => array(
				array(
					"message" => "Value for field 'foo_h' is not valid.",
					"value" => ""
				)
			),
			"foo_i" => array(
				array(
					"message" => "Please enter something.",
					"value" => ""
				)
			),
			"foo_j" => array(
				array(
					"message" => "Value for field 'foo_j' is not valid.",
					"value" => ""
				)
			),
			"foo_k" => array(
				array(
					"message" => "The value you entered must be between 1 and 10.",
					"value" => ""
				)
			),
			"foo_l" => array(
				"foo_l_2" => array(
					array(
						"message" => "Value for field 'foo_l_2' is not valid.",
						"value" => "bar_l_2"
					)
				),
				"foo_l_3" => array(
					array(
						"message" => "The value you entered must be a number.",
						"value" => "bar_l_3"
					)
				),
				"foo_l_5" => array(
					array(
						"message" => "Value for field 'foo_l_5' is not valid.",
						"value" => 3
					)
				),
				"foo_l_6" => array(
					array(
						"message" => "The value you entered must be between 8 and 10.",
						"value" => 3
					)
				),
				"foo_l_7" => array(
					"foo_l_7_2" => array(
						array(
							"message" => "Value for field 'foo_l_7_2' is not valid.",
							"value" => "bar_l_7_2"
						)
					),
					"foo_l_7_3" => array(
						array(
							"message" => "The value you entered must be a number.",
							"value" => "bar_l_7_3"
						)
					),
					"foo_l_7_5" => array(
						array(
							"message" => "Value for field 'foo_l_7_5' is not valid.",
							"value" => 3
						)
					),
					"foo_l_7_6" => array(
						array(
							"message" => "The value you entered must be between 8 and 10.",
							"value" => 3
						)
					),
					"foo_l_7_7" => array(
						array(
							"message" => "Value for field 'foo_l_7_7' is not valid.",
							"value" => ""
						)
					),
					"foo_l_7_8" => array(
						array(
							"message" => "Please enter something.",
							"value" => ""
						)
					),
					"foo_l_7_9" => array(
						array(
							"message" => "Value for field 'foo_l_7_9' is not valid.",
							"value" => ""
						)
					),
					"foo_l_7_10" => array(
						array(
							"message" => "The value you entered must be between 1 and 10.",
							"value" => ""
						)
					)
				),
				"foo_l_8" => array(
					array(
						"message" => "Value for field 'foo_l_8' is not valid.",
						"value" => ""
					)
				),
				"foo_l_9" => array(
					array(
						"message" => "Please enter something.",
						"value" => ""
					)
				),
				"foo_l_10" => array(
					array(
						"message" => "Value for field 'foo_l_10' is not valid.",
						"value" => ""
					)
				),
				"foo_l_11" => array(
					array(
						"message" => "The value you entered must be between 1 and 10.",
						"value" => ""
					)
				)
			),
		);

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);


		// Rule is PHP function
		$document = new Models\Document("test");
		$document->foo = "bar";

		$rules = array(
			"foo" => array(
				"rule" => "is_string"
			)
		);

		$expectedValidationErrors = array();

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);


		return $dataProvider;
	}
}
?>