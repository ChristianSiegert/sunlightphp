<?php
class AuthComponent {
	public $components = array("Session");

	protected $allowedActions = array();

	public $authError = "You need to sign in to access this page.";

	private $controller;

	private $params;


	public function __construct(&$controller) {
		$this->params = $controller->params;
		$this->controller = $controller;
	}

	public function startUp() {
		if (!$this->isAuthorized()) {
			$this->controller->Session->setFlash($this->authError, "auth");
			$this->controller->Session->data["auth"]["redirect"] = BASE_URL . $this->params["url"]["url"];
			$this->controller->redirect(array("controller" => "users", "action" => "sign-in"));
		}
	}

	public function allow() {
		$this->allowedActions = func_get_args();
	}

	public function isAuthorized() {
		// User is authorized if one of these conditions is true
		return isset($this->controller->Session->data["user"]["id"])
				|| in_array(str_replace("-", "_", $this->params["action"]), $this->allowedActions)
				|| ($this->params["controller"] === "users" && $this->params["action"] === "sign-in");
	}
}
?>