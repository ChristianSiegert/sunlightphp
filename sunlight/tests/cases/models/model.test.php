<?php
include(CORE_DIR . DS . "models" . DS . "model.php");

class ModelDataTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider validateDataProvider
	 */
	public function testValidate($document, $rules, $expected) {
		$model = new Model(new stdClass());

		$result = $model->validate($document, $rules);
		$this->assertSame($expected, $result);
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