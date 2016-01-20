<?php
/**
 * Bootstrap file
 *
 * Initialises libraries and configuration
 *
 */

define('DS', DIRECTORY_SEPARATOR);

setup_autoloader();

function setup_autoloader()
{
	Autoloader::add_classes([
		// The app
		'App'			=> COREPATH . 'classes/app.php',

		// The MVC framework
		'Model'			=> COREPATH . 'classes/model.php',
		'Controller'	=> COREPATH . 'classes/controller.php',
		'View'			=> COREPATH . 'classes/view.php',
		'ORM'			=> COREPATH . 'classes/orm.php',

		// Configuration and dependencies
		'Config'		=> COREPATH . 'classes/config.php',
		'Inflection'	=> COREPATH . 'classes/inflection.php',
		'Router'		=> COREPATH . 'classes/router.php',

		// Helpers
		'Arr'			=> COREPATH . 'classes/arr.php',
		'Date'			=> COREPATH . 'classes/date.php',
		'Debug'			=> COREPATH . 'classes/debug.php',
		'Cookie'		=> COREPATH . 'classes/cookie.php',
		'HTML'			=> COREPATH . 'classes/html.php',
		'Input'			=> COREPATH . 'classes/input.php',
	]);
}
