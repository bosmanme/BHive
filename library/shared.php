<?php
// Set inflection
$inflect = new Inflection();

/**
 * Auto class loader
 * @param string $className
 */
function __autoload($className)
{
    // Check if it's a class, controller or model
    if (file_exists(ROOT. DS . 'library' . DS . strtolower($className) . '.class.php')) {
        require_once ROOT. DS . 'library' . DS . strtolower($className) . '.class.php';
    } elseif (file_exists(ROOT . DS . 'app' . DS . 'controllers' . DS . strtolower($className) . '.php')) {
        require_once ROOT . DS . 'app' . DS . 'controllers' . DS . strtolower($className) . '.php';
    } elseif (file_exists(ROOT . DS . 'app' . DS . 'models' . DS . strtolower($className) . '.php')) {
        require_once ROOT . DS . 'app' . DS . 'models' . DS . strtolower($className) . '.php';
    } else {
        /* Check if a descendant model exists */
        $regex = '/(?<!^)((?<![[:upper:]])[[:upper:]]|[[:upper:]](?![[:upper:]]))/';
        $path = ROOT . DS . 'app' . DS . 'models' . DS . strtolower(preg_replace($regex, '/$1', $className)) . '.php';
        if (file_exists($path)) {
            require_once $path;
        } else {
            die ('no such class ' . $className . ' can be loaded');
        }
    }
}
				 
/**
 * Secondary call function
 * @param string $controller
 * @param string $action
 * @param array $queryString
 * @param boolean $render
 * @return mixed
 */
function performAction($controller,$action,$queryString = array(),$render = false) 
{	
    $controllerName = ucfirst($controller).'Controller';
    $dispatch = new $controllerName($controller,$action);
    $dispatch->render = $render;
    return call_user_func_array(array($dispatch,$action),$queryString);
}

/**
 * Redirects the browser using the php header
 * @param string $url
 */
function redirect($url)
{
    $url = BASE_PATH . $url;
    header('Location: ' . $url);
    exit();
}

/**
 * Debuff method
 * @param mixed $var
 * @param boolean $exit Stop execution?
 */
function d($var, $exit = true)
{
    print '<pre>';
    var_dump($var);
    print '</pre>';
    
    if ($exit) {
        exit;
    }
}
?>