<?php
class Controller {
	public $components = array();

	public $models = array();

	public $helpers = array("Asset", "Html", "Session");

	public $cacheActions = false;

	public $view = "";

	public $autoRender = true;

	public $removeWhitespace = true;

	public $passedVariables = array();

	public $params;

	public $data;

	public $validationErrors;

	public function __construct(&$params) {
		$this->params = $params;
		$this->data = !empty($_POST) ? $_POST : $_GET;
	}

	public function loadComponents() {
		// Load components
		for ($i = 0; $i < count($this->components); $i++) {
			include(CORE_DIR . DS . "controllers" . DS . "components" . DS . strtolower($this->components[$i]) . ".php");

			$componentClassName = $this->components[$i] . "Component";
			$componentObject = $this->{$this->components[$i]} = new $componentClassName($this);

			if (isset($componentObject->components)) {
				$this->components = array_unique(array_merge($this->components, $componentObject->components));
			}
		}

		// Initialize components
		foreach ($this->components as $component) {
			if (method_exists($this->$component, "initialize")) {
				$this->$component->initialize();
			}
		}
	}

	public function beforeFilter() {

	}

	public function startUpComponents() {
		foreach ($this->components as $component) {
			if (method_exists($this->$component, "startUp")) {
				$this->$component->startUp();
			}
		}
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

	public function render() {
		// Include core view file
		include(CORE_DIR . DS . "views" . DS . "view.php");

		// Load view
		$view = new View($this);
		$contentForLayout = $view->renderAction();
		$document = $view->renderLayout($contentForLayout, $this->removeWhitespace);

		return $document;
	}

	/**
	 * Sets the variables that can be accessed in a view.
	 *
	 * @param array Associative array
	 */
	public function set($variable) {
		$this->passedVariables = array_merge($this->passedVariables, $variable);
	}

	public function redirect($url, $replaceHeader = true, $httpStatusCode = 302) {
		if (is_array($url)) {
			$url = Router::url($url);
		}

		header("Location: $url", $replaceHeader, $httpStatusCode);
		exit;
	}
}
?>