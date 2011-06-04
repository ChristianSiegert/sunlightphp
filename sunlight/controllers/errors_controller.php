<?php
namespace Controllers;

class ErrorsController extends AppController {
	public $components = array("Session");

	public $cacheActions = true;

	public function error404() {

	}
}
?>