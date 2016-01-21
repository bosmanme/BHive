<?php
/**
* Routing file
*
* Enables specific default controllers and actions. Also used to specify custom
* redirects using regular expressions.
*
*/

class Router
{

	public $url;
	public $default;

	public $queryString;

	public function route($url = null)
	{
		if ( ! $url) {
			$this->url = Input::server('QUERY_STRING');
		} else {
			$this->url = $url;
		}
		// Check if the current url is defined in the config routes
		//TODO: move this to the App::_init?
		Config::load('routes.php');
		$routing = Config::get('routes', []);

		foreach ($routing as $pattern => $route) {
			if (preg_match($pattern, $this->url)) {
				$this->url = preg_replace($pattern, $route, $this->url);
			}
		}

		// Split the url
		$urlArray = [];
		$urlArray = explode('/', $this->url);

		// Strip the first slash
		array_shift($urlArray);

		// The first part is the controller name
		$controller = isset($urlArray[0]) ? array_shift($urlArray) : null;

		// The second part is the method
		$action = isset($urlArray[0]) ? array_shift($urlArray) : null;

		// The third part are extra parameters
		$this->queryString = $urlArray;

		// if the controller is empty, redirect to the default controller
		if (empty($controller)) {
				$controller = 'home';
		}

		// if no method/action is given redirect to index
		if (empty($action)) {
			$action = 'index';
		}

		if ( ! static::performAction($controller, $action, $this->queryString)) {
			static::performAction('home', 'error');
		}
	}

	/**
	 * Secondary call function
	 * @param string $controller
	 * @param string $action
	 * @param array $queryString
	 * @return mixed
	 */
	public static function performAction($controller, $action, $queryString = [])
	{
	    $controllerName = 'Controller_' . ucfirst($controller);

		if (class_exists($controllerName)) {

		    $dispatch = new $controllerName($controller,$action);

			if ((int)method_exists($controllerName, $action)) {
				call_user_func_array(array($dispatch, 'beforeAction'), $queryString);
				call_user_func_array(array($dispatch, $action), $queryString);
				call_user_func_array(array($dispatch, 'afterAction'), $queryString);
			}

		} else {
			return false;
		}
	}

}
