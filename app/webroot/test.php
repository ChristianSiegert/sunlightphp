<?php
// Prevent remote access
if (!isset($_SERVER["SERVER_ADDR"])
		|| !isset($_SERVER["REMOTE_ADDR"])
		|| $_SERVER["SERVER_ADDR"] !== $_SERVER["REMOTE_ADDR"]) {
	exit;
}

mb_internal_encoding("UTF-8");

define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("APP_DIR", dirname(dirname(__FILE__)));
define("CORE_DIR", ROOT_DIR . DS . "sunlight");

define("APP_TEST_CASES_DIR", APP_DIR . DS . "tests" . DS . "cases");
define("CORE_TEST_CASES_DIR", CORE_DIR . DS . "tests" . DS . "cases");

function getFilenames($directory) {
	$filenames = array();

	if ($handle = opendir($directory)) {
		while (($file = readdir($handle)) !== false) {
			if ($file !== "." && $file !== ".." && $file !== "empty") {
				if (is_file($directory . DS . $file)) {
					$filenames[] = preg_replace('#^(?:' . ROOT_DIR . ')/#', "", $directory . DS . $file);
				} elseif (is_dir($directory . DS . $file)) {
					$filenames = array_merge($filenames, getFilenames($directory . DS . $file));
				}
			}
		}
		closedir($handle);
	}

	return $filenames;
}
?>

<?php if (!isset($_GET["file"])) { ?>
	<h2>App tests</h2>
	<ul>
		<?php
			$filenames = getFilenames(APP_TEST_CASES_DIR);

			foreach ($filenames as $fileName) {
				echo '<li><a href="?file=' . urlencode($fileName) . '">' . htmlentities($fileName, ENT_QUOTES, mb_internal_encoding()) . '</a></li>';
			}
		?>
	</ul>

	<h2>Core tests</h2>
	<ul>
		<?php
			$filenames = getFilenames(CORE_TEST_CASES_DIR);

			foreach ($filenames as $filename) {
				echo '<li><a href="?file=' . urlencode($filename) . '">' . htmlentities($filename, ENT_QUOTES, mb_internal_encoding()) . '</a></li>';
			}
		?>
	</ul>
<?php } else {
	$bootstrapFile = CORE_DIR . DS . "tests" . DS . "bootstrap.php";
	$testCaseFile = ROOT_DIR . DS . $_GET["file"];
	print_r($testCaseFile);
	$output = shell_exec("phpunit --verbose --bootstrap $bootstrapFile $testCaseFile");
	echo "<pre>" . htmlentities($output, ENT_QUOTES, mb_internal_encoding()) . "</pre>";
} ?>