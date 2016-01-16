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

// Load this packages Autoloader
require_once COREPATH . DS . 'classes' . DS . 'autoloader.php';

setup_autoloader();
Autoloader::register();

require_once COREPATH . DS . 'shared.php';

// Check if the Js, CSS and upload foler exist. If not: create
$folders = [FOLDER_JS, FOLDER_CSS, FOLDER_UPLOADS];
foreach ($folders as $folder) {
	if (!file_exists($folder)) {
		mkdir($folder);
	}
}

function setup_autoloader()
{
	Autoloader::add_classes([
		'App'			=> COREPATH . 'classes/app.php',
		'Controller'	=> COREPATH . 'classes/controller.php',
		'Date'			=> COREPATH . 'classes/date.php',
		'Debug'			=> COREPATH . 'classes/debug.php',
		'HTML'			=> COREPATH . 'classes/html.php',
		'Inflection'	=> COREPATH . 'classes/inflection.php',
		'Model'			=> COREPATH . 'classes/model.php',
		'ORM'			=> COREPATH . 'classes/orm.php',
		'Router'		=> COREPATH . 'classes/router.php',
		'View'			=> COREPATH . 'classes/view.php',
	]);
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
