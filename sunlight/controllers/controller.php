<?php
class Controller {
	public $components = array();

	public $data = array();

	public $helpers = array("Asset", "Html", "Session");

	public $params;

	public $passedVariables = array();

	public $validationErrors;

	public $autoRender = true;

	public $cacheActions = false;

	public $loadModel = true;

	function __construct(&$params) {
		$this->params = $params;

		if (isset($_POST["data"])) {
			$this->data = $_POST["data"];
		}
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

		// Initialize() components
		foreach ($this->components as $component) {
			if (method_exists($this->$component, "initialize")) {
				$this->$component->initialize();
			}
		}
	}

	public function beforeFilter() {

	}

	public function startUpComponents() {
		// StartUp() components
		foreach ($this->components as $component) {
			if (method_exists($this->$component, "startUp")) {
				$this->$component->startUp();
			}
		}
	}

	public function loadModels() {
		// Include model files
		include(CORE_DIR . DS . "models" . DS . "model.php");
		include(CORE_DIR . DS . "models" . DS . "app_model.php");
		include(APP_DIR . DS . "models" . DS . Inflector::singularize($this->params["controller"]) . ".php");

		// Load model
		$modelClassName = ucfirst(Inflector::singularize($this->params["controller"]));
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

	public function render() {
		// Load view file
		include(CORE_DIR . DS . "views" . DS . "view.php");

		// Create view object
		$view = new View($this);
		$contentForLayout = $view->renderAction();
		$document = $view->renderLayout($contentForLayout);

		return $document;
	}

	public function set($variable) {
		$this->passedVariables = array_merge($this->passedVariables, $variable);
	}

	public function redirect($url) {
		header("Location: $url");
		exit;
	}
}
?>