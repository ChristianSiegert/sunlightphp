<?php
class ErrorsController extends AppController {
	public $components = array("Session");

	public $loadModel = false;

	public $cacheActions = true;

	public function error_404() {

	}
}
?>