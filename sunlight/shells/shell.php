<?php
class Shell {
	public $models = array();

	public $params;

	public $validationErrors;

	public function __construct($params) {
		$this->params = $params;
	}

	public function beforeFilter() {

	}

	public function loadModels() {
		if (!empty($this->models)) {
			// Include model file
			include(CORE_DIR . DS . "models" . DS . "model.php");

			// Include app model file
			$appModelFile = DS . "models" . DS . "app_model.php";
			include(is_file(APP_DIR . $appModelFile) ? APP_DIR . $appModelFile : CORE_DIR . $appModelFile);

			foreach ($this->models as $modelName) {
				// Include custom model file
				include(APP_DIR . DS . "models" . DS . strtolower($modelName) . ".php");

				// Load model
				$model = $this->$modelName = new $modelName($this);

				// Load models required by this model
				for ($i = 0; $i < count($model->models); $i++) {
					include(APP_DIR . DS . "models" . DS . strtolower($model->models[$i]) . ".php");

					$requiredModel = $model->{$model->models[$i]} = new $model->models[$i]($this);

					if (!empty($requiredModel->models)) {
						$this->models = array_unique(array_merge($model->models, $requiredModel->models));
					}
				}
			}
		}
	}
}
?>