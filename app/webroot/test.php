<?php
mb_internal_encoding("UTF-8");

define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("CORE_DIR", ROOT_DIR . DS . "sunlight");
define("TESTS_DIR", CORE_DIR  . DS . "tests");
define("TEST_CASES_DIR", TESTS_DIR . DS . "cases");
?>

<?php if (!isset($_GET["file"])) { ?>
	<ul>
		<?php
		if ($handle = opendir(TEST_CASES_DIR)) {
			while (($file = readdir($handle)) !== false) {
				if ($file !== "." && $file !== "..") {
					echo '<li><a href="?file=' . urlencode($file) . '">' . htmlentities($file, ENT_QUOTES, mb_internal_encoding()) . '</a></li>';
				}
			}
			closedir($handle);
		}
		?>
	</ul>
<?php } else {
	$bootstrapFile = TESTS_DIR . DS . "bootstrap.php";
	$testCaseFile = TEST_CASES_DIR . DS . $_GET["file"];
	$output = shell_exec("phpunit --verbose --bootstrap $bootstrapFile $testCaseFile");
	echo "<pre>" . htmlentities($output, ENT_QUOTES, mb_internal_encoding()) . "</pre>";
} ?>