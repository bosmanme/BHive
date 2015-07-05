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

// Check if the Js, CSS and upload foler exist. If not: create
$folders = [FOLDER_JS, FOLDER_CSS, FOLDER_UPLOADS];
foreach ($folders as $folder) {
	if (!file_exists(ROOT . DS . 'public' . DS . $folder)) {
		mkdir(ROOT . DS . 'public' . DS . $folder);
	}
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