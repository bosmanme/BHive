<?php
/**
 * Bootstrap file
 *
 * Initialises libraries and configuration
 *
 */

session_start();

require_once 'config' . DS . 'config.php';
require_once 'config' . DS . 'inflection.php';
require_once 'config' . DS . 'routes.php';
require_once 'library' . DS . 'shared.php';

// Check if the Js, CSS and upload foler exist. If not: create
$folders = [FOLDER_JS, FOLDER_CSS, FOLDER_UPLOADS];
foreach ($folders as $folder) {
	if (!file_exists($folder)) {
		mkdir($folder);
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
