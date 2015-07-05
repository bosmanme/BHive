<?php
/**
* Routing file
*
* Enables specific default controllers and actions. Also used to specify custom
* redirects using regular expressions.
*
*/

class Router {
	
	public $url;
	public $default;
	
	public $queryString;

	public function route($url) {
		
		$this->url = $url;
		
		// Split the url
		$urlArray = array();
		$urlArray = explode('/', $this->url);
		
		// The first part is the controller name
		$controller = isset($urlArray[0]) ? array_shift($urlArray) : null;
		
		// The second part is the method
		$action = isset($urlArray[0]) ? array_shift($urlArray) : null;
		
		// The third part are extra parameters
		$this->queryString = $urlArray;
		
		// if the controller is empty, redirect to the default controller
		if (empty($controller)) {
				$controller = DEFAULT_CONTROLLER;
		}
		
		// if no method/action is given redirect to index
		if (empty($action)) {
			$action = 'index';	
		}
		
		$controllerName = ucfirst($controller) . 'Controller';
		if (class_exists($controllerName)) {
			$dispatch = new $controllerName($controller, $action);
			
			if ((int)method_exists($controllerName, $action)) {
				call_user_func_array(array($dispatch, 'beforeAction'), $this->queryString);
				call_user_func_array(array($dispatch, $action), $this->queryString);
				call_user_func_array(array($dispatch, 'afterAction'), $this->queryString);
			}
		} else {
			performAction(DEFAULT_CONTROLLER, 'error', array(), true);
			
		}
	}
	
}
?>