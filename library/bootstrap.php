<?php
/**
 * Bootstrap file
 * 
 * Initialises libraries and configuration
 * 
 */

session_start();

require_once ROOT . DS . 'config' . DS . 'config.php';
require_once ROOT . DS . 'config' . DS . 'inflection.php';
require_once ROOT . DS . 'library' . DS . 'shared.php';

// Check if the upload folder exists. If not: create
if (!file_exists(ROOT . DS . UPLOAD_FOLDER)) {
    mkdir(ROOT . DS . UPLOAD_FOLDER);
}

// Route the requested url
$router = new Router();
if (isset($_GET['url'])) {
    $url = $_GET['url'];
} else {
    $url = '';
}
$router->route($url);
?>