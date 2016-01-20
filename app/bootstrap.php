<?php
// Bootstrap the framework
require COREPATH . 'bootstrap.php';

Autoloader::add_classes([
    // Add classes to override here
    // Example: 'View' => APPATH . 'classes/view.php',
]);

// Register the autoloader
Autoloader::register();

App::init('config.php');

// Route the requested url
$router = new Router();
$router->route();
