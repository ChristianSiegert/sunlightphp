<?php
namespace Models;

class DocumentException extends \Exception {
	const HAS_VALIDATION_ERRORS = 1;
	const MISSING_WHITELIST = 2;
	const MISSING_VALIDATION_RULES = 3;
	const NON_WHITELISTED_FIELD_PRESENT = 4;
}
?>