<?php
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
    $controllerName = 'Controller_' . ucfirst($controller);
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
    header('Location: ' . $url);
    exit();
}
