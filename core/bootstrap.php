<?php
/**
 * Bootstrap file
 *
 * Initialises libraries and configuration
 *
 */

define('DS', DIRECTORY_SEPARATOR);

setup_autoloader();

// Load base functions
require COREPATH . 'base.php';

function setup_autoloader()
{
	Autoloader::addNamespace('BHive\\Core', COREPATH . 'classes/');

	Autoloader::addClasses([
		// The app
		'BHive\\Core\\App'			=> COREPATH . 'classes/app.php',

		// The MVC framework
		'BHive\\Core\\Model'			=> COREPATH . 'classes/model.php',
		'BHive\\Core\\Controller'		=> COREPATH . 'classes/controller.php',
		'BHive\\Core\\View'				=> COREPATH . 'classes/view.php',
		'BHive\\Core\\ORM'				=> COREPATH . 'classes/orm.php',

		// Configuration and dependencies
		'BHive\\Core\\Config'			=> COREPATH . 'classes/config.php',
		'BHive\\Core\\Inflection'		=> COREPATH . 'classes/inflection.php',
		'BHive\\Core\\Router'			=> COREPATH . 'classes/router.php',

		// Helpers
		'BHive\\Core\\Arr'				=> COREPATH . 'classes/arr.php',
		'BHive\\Core\\Asset'			=> COREPATH . 'classes/asset.php',
		'BHive\\Core\\Cookie'			=> COREPATH . 'classes/cookie.php',
		'BHive\\Core\\Crypt'			=> COREPATH . 'classes/crypt.php',
		'BHive\\Core\\Date'				=> COREPATH . 'classes/date.php',
		'BHive\\Core\\Debug'			=> COREPATH . 'classes/debug.php',
		'BHive\\Core\\File'				=> COREPATH . 'classes/file.php',
		'BHive\\Core\\Input'			=> COREPATH . 'classes/input.php',
		'BHive\\Core\\Str'				=> COREPATH . 'classes/str.php',

		'BHive\\Core\\Validation'		=> COREPATH . 'classes/validation.php',
		'BHive\\Core\\Validation_Error'	=> COREPATH . 'classes/validation/error.php',
	]);
}
