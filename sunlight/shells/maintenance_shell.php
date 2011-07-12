<?php
namespace Shells;

use Models\AppDocument;
use Models\CouchDb\CouchDbDatabase;
use Models\DatabaseException;
use Models\DocumentException;

class MaintenanceShell extends AppShell {
	public function install() {
		$this->createDatabase(DATABASE_HOST, DATABASE_NAME);
		$this->uploadDesignDocuments();
	}

	public function createDatabase($databaseHost = "", $databaseName = "") {
		echo "\nCreating database ... ";

		if (isset($this->params["named"]["host"])) {
			$databaseHost = $this->params["named"]["host"];
		}

		if (isset($this->params["named"]["name"])) {
			$databaseName = $this->params["named"]["name"];
		}

		if (!$databaseHost || !$databaseName) {
			exit("Please provide a database host and database name, e.g. 'create-database host:http://localhost:5984 name:foobar'.\n");
		}

		try {
			CouchDbDatabase::create($databaseHost, $databaseName);
		} catch (DatabaseException $exception) {
			exit($exception->getMessage() . "\n");
		}

		echo "Done.\n";
	}

	public function uploadDesignDocuments() {
		echo "\nUploading design documents ... ";

		$count = 0;

		$directory = APP_DIR . DS . "models" . DS . "designs";
		$baseNames = $this->getFilenames($directory);

		foreach ($baseNames as $baseName) {
			echo "\nIncluding file " . $directory . DS . $baseName . " ...";
			require $directory . DS . $baseName;
			echo " Done.\n";

			if (!isset($design)) {
				echo "WARNING: $baseName does not contain variable '\$design'.\n";
				continue;
			}

			echo "Uploading design document '" . $design["_id"] . "' ...";

			$designDocument = new AppDocument();
			$designDocument->_id = $design["_id"];

			// If design already exists in database, update it
			try {
				$designDocument->fetch();
				$designDocument->setWhitelist(false);
				$designDocument->merge($design);
			// Else, create it
			} catch (DocumentException $exception) {
				$designDocument = AppDocument::createFromArray($design);
			}

			$designDocument->setValidationRules(false);
			$designDocument->save();

			unset($design);
			echo " Done.\n";
			$count++;
		}

		echo "Done (Uploaded " . $count . " of " . count($baseNames) . " design documents).\n";
	}

	protected function getFilenames($directory) {
		$filenames = array();

		if ($handle = opendir($directory)) {
		    while (false !== ($file = readdir($handle))) {
		        if ($file !== "empty" && $file !== "." && $file !== "..") {
					array_push($filenames, $file);
		        }
		    }

    		closedir($handle);
		}

		return $filenames;
	}
}
?>