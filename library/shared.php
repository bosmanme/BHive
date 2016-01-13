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
    if (file_exists('library' . DS . 'classes' . DS . strtolower($className) . '.php')) {
        require_once 'library' . DS . 'classes' . DS . strtolower($className) . '.php';
    } elseif (file_exists('app' . DS . 'controllers' . DS . strtolower($className) . '.php')) {
        require_once 'app' . DS . 'controllers' . DS . strtolower($className) . '.php';
    } elseif (file_exists('app' . DS . 'models' . DS . strtolower($className) . '.php')) {
        require_once 'app' . DS . 'models' . DS . strtolower($className) . '.php';
    } else {
        /* Check if a descendant model exists base on camelcasing
        * For example: UserRole will be in models / user / role.php
        */
        $regex = '/(?<!^)((?<![[:upper:]])[[:upper:]]|[[:upper:]](?![[:upper:]]))/';
        $path = 'app' . DS . 'models' . DS . strtolower(preg_replace($regex, '/$1', $className)) . '.php';
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
function performAction($controller,$action,$queryString = [],$render = false)
{
    $controllerName = ucfirst($controller).'Controller';
    $dispatch = new $controllerName($controller,$action);
    $dispatch->render = $render;
    return call_user_func_array([$dispatch,$action],$queryString);
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
?>
