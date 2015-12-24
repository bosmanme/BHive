<?php
/**
 * Configuration file
 *
 * Configuration settings used globabbly
 *
 */

// Name of your site
$sitename = 'Mtc';

// Default language
$language = 'Nl';

// Default timezone used
define('DEFAULT_TIMEZONE', 'Europe/Brussels');

// Default charset of your site
define('CHARSET', 'UTF-8');

// Default Javascript, CSS and upload folder. If they do not exist, they will be created
define('FOLDER_JS', 'assets/js');
define('FOLDER_CSS', 'assets/css');
define('FOLDER_UPLOADS', 'assets/uploads');

// Database settings
define('DB_NAME', 'mtc');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
define('DB_PORT', '');
define('DB_PREF', '');

// Base path, the absolute path of your project
define('BASE_PATH', '');

define('SITE_NAME', $sitename);

// Default controller
define('DEFAULT_CONTROLLER', 'home');

// Others
define('DEVELOPMENT_ENVIRONMENT', true);
define('DEFAULT_LANGUAGE', $language);
?>
