<?php
include(CORE_DIR . DS . "models" . DS . "model.php");

class ModelDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider onlyContainsWhitelistedFieldsDataProvider
	 */
	public function testOnlyContainsWhitelistedFields($document, $fieldList, $expectedErrors) {
		$result = Model::onlyContainsWhitelistedFields($document, $fieldList);
		$this->assertSame($expectedErrors, $result);
	}

	public function onlyContainsWhitelistedFieldsDataProvider() {
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

		// Not all fields are whitelisted
		$document = array(
			// Nesting levels: 0
			"foo_1" => null,
			"foo_2" => false,
			"foo_3" => true,
			"foo_4" => 0,
			"foo_5" => 1,
			"foo_6" => 2.3,
			"foo_7" => "",
			"foo_8" => "blah",
			"foo_9" => array(),

			// Nesting levels: 1 (indexed array)
			"foo_10" => array(null),
			"foo_11" => array(false),
			"foo_12" => array(true),
			"foo_13" => array(0),
			"foo_14" => array(1),
			"foo_15" => array(2.3),
			"foo_16" => array(""),
			"foo_17" => array("blah"),
			"foo_18" => array(array()),

			// Nesting levels: 1 (associative array)
			"foo_19" => array("foo_19_1" => null),
			"foo_20" => array("foo_20_1" => false),
			"foo_21" => array("foo_21_1" => true),
			"foo_22" => array("foo_22_1" => 0),
			"foo_23" => array("foo_23_1" => 1),
			"foo_24" => array("foo_24_1" => 2.3),
			"foo_25" => array("foo_25_1" => ""),
			"foo_26" => array("foo_26_1" => "blah"),
			"foo_27" => array("foo_27_1" => array()),

			// Nesting levels: 2 (indexed array, indexed array)
			"foo_28" => array(array(null)),
			"foo_29" => array(array(false)),
			"foo_30" => array(array(true)),
			"foo_31" => array(array(0)),
			"foo_32" => array(array(1)),
			"foo_33" => array(array(2.3)),
			"foo_34" => array(array("")),
			"foo_35" => array(array("blah")),
			"foo_36" => array(array(array())),

			// Nesting levels: 2 (indexed array, associative array)
			"foo_37" => array(array("foo_37_1_1" => null)),
			"foo_38" => array(array("foo_38_1_1" => false)),
			"foo_39" => array(array("foo_39_1_1" => true)),
			"foo_40" => array(array("foo_40_1_1" => 0)),
			"foo_41" => array(array("foo_41_1_1" => 1)),
			"foo_42" => array(array("foo_42_1_1" => 2.3)),
			"foo_43" => array(array("foo_43_1_1" => "")),
			"foo_44" => array(array("foo_44_1_1" => "blah")),
			"foo_45" => array(array("foo_45_1_1" => array())),

			// Nesting levels: 2 (associative array, indexed array)
			"foo_46" => array("foo_46_1" => array(null)),
			"foo_47" => array("foo_47_1" => array(false)),
			"foo_48" => array("foo_48_1" => array(true)),
			"foo_49" => array("foo_49_1" => array(0)),
			"foo_50" => array("foo_50_1" => array(1)),
			"foo_51" => array("foo_51_1" => array(2.3)),
			"foo_52" => array("foo_52_1" => array("")),
			"foo_53" => array("foo_53_1" => array("blah")),
			"foo_54" => array("foo_54_1" => array(array())),

			// Nesting levels: 2 (associative array, associative array)
			"foo_55" => array("foo_55_1" => array("foo_55_1_1" => null)),
			"foo_56" => array("foo_56_1" => array("foo_56_1_1" => false)),
			"foo_57" => array("foo_57_1" => array("foo_57_1_1" => true)),
			"foo_58" => array("foo_58_1" => array("foo_58_1_1" => 0)),
			"foo_59" => array("foo_59_1" => array("foo_59_1_1" => 1)),
			"foo_60" => array("foo_60_1" => array("foo_60_1_1" => 2.3)),
			"foo_61" => array("foo_61_1" => array("foo_61_1_1" => "")),
			"foo_62" => array("foo_62_1" => array("foo_62_1_1" => "blah")),
			"foo_63" => array("foo_63_1" => array("foo_63_1_1" => array())),
		);

		$fieldList = array(
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

			"foo_37" => array("foo_37_1"),
			"foo_38" => array("foo_38_1"),
			"foo_39" => array("foo_39_1"),
			"foo_40" => array("foo_40_1"),
			"foo_41" => array("foo_41_1"),
			"foo_42" => array("foo_42_1"),
			"foo_43" => array("foo_43_1"),
			"foo_44" => array("foo_44_1"),
			"foo_45" => array("foo_45_1"),

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

		// 0 document fields, 0 corresponding fields whitelisted, 0 non-corresponding fields whitelisted
		$document2 = array();

		$fieldList2 = array();

		$expected2 = true;

		// 1 document field, 0 corresponding fields whitelisted, 0 non-corresponding fields whitelisted
		$document3 = array(
			"foo_1" => "bar_1"
		);

		$fieldList3 = array();

		$expected3 = "foo_1";

		// 1 document fields, 0 corresponding fields whitelisted, 1 non-corresponding field whitelisted
		$document4 = array(
			"foo_1" => "bar_1",
		);

		$fieldList4 = array(
			"foo_2"
		);

		$expected4 = "foo_1";

		// 1 document fields, 1 corresponding field whitelisted, 0 non-corresponding fields whitelisted
		$document5 = array(
			"foo_1" => "bar_1"
		);

		$fieldList5 = array(
			"foo_1"
		);

		$expected5 = true;

		// 1 document fields, 1 corresponding field whitelisted, 1 non-corresponding field whitelisted
		$document6 = array(
			"foo_1" => "bar_1"
		);

		$fieldList6 = array(
			"foo_1",
			"foo_2"
		);

		$expected6 = true;

		// 1 document field with 1 nested field, 0 corresponding field whitelisted, 0 non-corresponding field whitelisted
		$document7 = array(
			"foo_1" => array(
				"foo_1_1" => "bar_1_1"
			)
		);

		$fieldList7 = array();

		$expected7 = "foo_1";

		// 1 document field with 1 nested field, 0 corresponding field whitelisted, 1 non-corresponding field whitelisted
		$document8 = array(
			"foo_1" => array(
				"foo_1_1" => "bar_1_1"
			)
		);

		$fieldList8 = array(
			"foo_1" => array(
				"foo_1_2"
			)
		);

		$expected8 = "foo_1_1";

		// 1 document field with 1 nested field, 1 corresponding field whitelisted, 0 non-corresponding field whitelisted
		$document9 = array(
			"foo_1" => array(
				"foo_1_1" => "bar_1_1"
			)
		);

		$fieldList9 = array(
			"foo_1" => array(
				"foo_1_1"
			)
		);

		$expected9 = true;

		// 1 document field with 1 nested field, 1 corresponding field whitelisted, 1 non-corresponding field whitelisted
		$document10 = array(
			"foo_1" => array(
				"foo_1_1" => "bar_1_1"
			)
		);

		$fieldList10 = array(
			"foo_1" => array(
				"foo_1_1",
				"foo_1_2"
			)
		);

		$expected10 = true;

		return array(
			/*array($case1, true),
			array($case2, true),
			array($case3, true),
			array($case4, true),
			array($case5, true),
			array($case6, true),
			array($case7, "foo"),
			array($case8, "foo"),
			array($case9, "bar"),
			array($case10, "bar"),
			array($case11, "moo"),
			array($case12, "moo"),*/
			#array($case13, true),
			array($document, $fieldList, $expected),
			array($document2, $fieldList2, $expected2),
			array($document3, $fieldList3, $expected3),
			array($document4, $fieldList4, $expected4),
			array($document5, $fieldList5, $expected5),
			array($document6, $fieldList6, $expected6),
			array($document7, $fieldList7, $expected7),
			array($document8, $fieldList8, $expected8),
			array($document9, $fieldList9, $expected9),
			array($document10, $fieldList10, $expected10),
		);
	}

	/**
	 * @dataProvider validateDataProvider
	 */
	public function testValidate($document, $rules, $expectedValidationErrors) {
		$model = new Model(new stdClass());

		$result = $model->validate($document, $rules);
		$this->assertSame($expectedValidationErrors, $result);
	}

	public function validateDataProvider() {
		$document = array(
			"foo_a" => "bar_a",
			"foo_b" => "bar_b",
			"foo_c" => "bar_c",
			"foo_d" => 3,
			"foo_e" => 3,
			"foo_f" => 3,
			"foo_g" => array(
				"foo_g_1" => "bar_g_1",
				"foo_g_2" => "bar_g_2",
				"foo_g_3" => "bar_g_3",
				"foo_g_4" => 3,
				"foo_g_5" => 3,
				"foo_g_6" => 3,
				"foo_g_7" => array(
					"foo_g_7_1" => "bar_g_7_1",
					"foo_g_7_2" => "bar_g_7_2",
					"foo_g_7_3" => "bar_g_7_3",
					"foo_g_7_4" => 3,
					"foo_g_7_5" => 3,
					"foo_g_7_6" => 3,
				)
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
			)
		);

		return array(
			array($document, $rules, $expectedValidationErrors)
		);
	}
}
?>