<?php
class Shell {
	public $loadModel = true;

	public $params;

	public $validationErrors;

	public function __construct($params) {
		$this->params = $params;
	}

	public function beforeFilter() {

	}

	public function loadModel() {
		// Include model file
		include(CORE_DIR . DS . "models" . DS . "model.php");

		// Include app model file
		$appModelFile = DS . "models" . DS . "app_model.php";

		if (file_exists(APP_DIR . $appModelFile)) {
			include(APP_DIR . $appModelFile);
		} else {
			include(CORE_DIR . $appModelFile);
		}

		// Include custom model file
		include(APP_DIR . DS . "models" . DS . Inflector::singularize($this->params["shell"]) . ".php");

		// Load model
		$modelClassName = ucfirst(Inflector::singularize($this->params["shell"]));
		$model = $this->$modelClassName = new $modelClassName($this);

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
?>