<?php
/**
 * Website document root
 */
define('DOCROOT', __DIR__ . DIRECTORY_SEPARATOR);

/**
 * Path to the application directory.
 */
define('APPPATH', realpath(__DIR__.'/app/') . DIRECTORY_SEPARATOR);

/**
 * The path to the framework core.
 */
define('COREPATH', realpath(__DIR__.'/core/') . DIRECTORY_SEPARATOR);

// Activate the framework class autoloader
require COREPATH . 'classes' . DIRECTORY_SEPARATOR . 'autoloader.php';
class_alias('BHive\\Core\\Autoloader', 'Autoloader');

// Boot the app
require_once APPPATH . 'bootstrap.php';
