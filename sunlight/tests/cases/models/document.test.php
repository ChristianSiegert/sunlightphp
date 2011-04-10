<?php
include CORE_DIR . DS . "models" . DS . "couchdb" . DS . "couch_db.php";
include CORE_DIR . DS . "models" . DS . "couchdb" . DS . "couch_db_document.php";
include CORE_DIR . DS . "models" . DS . "document.php";

// Our fake database info
define("DATABASE_HOST", "empty");
define("DATABASE_NAME", "empty");

class DocumentDataTest extends PHPUnit_Framework_TestCase {
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
		$document = new Document(new stdClass(), "test");
		$rules = array();
		$expectedValidationErrors = array();

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);

		// 0 fields, 1 rule, 1 expected validation error
		$document = new Document(new stdClass(), "test");

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
		$document = new Document(new stdClass(), "test");
		$document->foo = 3;

		$rules = array(
			"foo" => array(
				"rule" => "isNumeric"
			)
		);

		$expectedValidationErrors = array();

		$dataProvider[] = array($document, $rules, $expectedValidationErrors);

		// 1 field, 1 rule, 1 expected validation error
		$document = new Document(new stdClass(), "test");
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

		$document = new Document(new stdClass(), "test");
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

		return $dataProvider;
	}
}
?>