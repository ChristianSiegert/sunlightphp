<?php
define("DS", DIRECTORY_SEPARATOR);
define("ROOT_DIR", dirname(dirname(dirname(__FILE__))));
define("CORE_DIR", ROOT_DIR . DS . "sunlight");
define("TEST_CASES_DIR", CORE_DIR  . DS . "tests" . DS . "cases");
?>

<?php if (!isset($_GET["file"])) { ?>
	<ul>
		<?php
		if ($handle = opendir(TEST_CASES_DIR)) {
			while (($file = readdir($handle)) !== false) {
				if ($file !== "." && $file !== "..") {
					echo '<li><a href="?file=' . $file . '">' . $file . '</a></li>';
				}
			}
			closedir($handle);
		}
		?>
	</ul>
<?php } else {
	$output = shell_exec("phpunit SanitizerTest " . TEST_CASES_DIR . DS . $_GET["file"]);
	echo "<pre>" . htmlentities($output) . "</pre>";
}