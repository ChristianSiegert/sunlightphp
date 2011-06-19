<?php
namespace Shells;

use Models\AppDocument;
use Models\DocumentException;

class MaintenanceShell extends AppShell {
	public function uploadDesigns() {
		$directory = APP_DIR . DS . "models" . DS . "designs";

		foreach ($this->getFilenames($directory) as $baseName) {
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
		}

		echo "\nCompleted uploading design documents.";
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