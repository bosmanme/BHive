<?php
/**
 * Configuration file
 * 
 * Configuration settings used globabbly
 * 
 */

// Name of your site
$sitename = 'Mtc';


// Default timezone used
define('DEFAULT_TIMEZONE', 'Europe/Brussels');

// Default charset of your site
define('CHARSET', 'UTF-8');

// Folder used to upload user uploads, if it does not exist it will be created
define('UPLOAD_FOLDER', 'uploads');

// Database settings
define('DB_NAME', 'mtc');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
define('DB_PORT', '');
define('DB_PREF', '');

// Base path, the absolute path of your project
define('BASE_PATH', 'http://localhost/' . $sitename);
define('SITE_NAME', $sitename);

// Default controller
define('DEFAULT_CONTROLLER', 'home');

// Set development environment
define('DEVELOPMENT_ENVIRONMENT', true);
?>