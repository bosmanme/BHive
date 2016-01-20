<?php
/**
 * Bootstrap file
 *
 * Initialises libraries and configuration
 *
 */

define('DS', DIRECTORY_SEPARATOR);

setup_autoloader();

// Load shared functions
require_once COREPATH . 'shared.php';

function setup_autoloader()
{
	Autoloader::add_classes([
		'App'			=> COREPATH . 'classes/app.php',
		'Arr'			=> COREPATH . 'classes/arr.php',
		'Controller'	=> COREPATH . 'classes/controller.php',
		'Date'			=> COREPATH . 'classes/date.php',
		'Debug'			=> COREPATH . 'classes/debug.php',
		'Config'		=> COREPATH . 'classes/config.php',
		'HTML'			=> COREPATH . 'classes/html.php',
		'Inflection'	=> COREPATH . 'classes/inflection.php',
		'Model'			=> COREPATH . 'classes/model.php',
		'ORM'			=> COREPATH . 'classes/orm.php',
		'Router'		=> COREPATH . 'classes/router.php',
		'View'			=> COREPATH . 'classes/view.php',
	]);
}
