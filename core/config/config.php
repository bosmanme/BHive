<?php
/**
 * Part of the BHive framework.
 *
 * @package    BHive
 * @version    1.0
 * @author     Mathias Bosman
 * @license    MIT License
 * @copyright  2016 - Mathias Bosman
 */

return [
	/**
	 * The app's name
	 */
	'app_name' => 'BHive',
	
    /**
	 * base_url - The base URL of the application.
	 * MUST contain a trailing slash (/)
	 *
	 * You can set this to a full or relative URL:
	 *
	 *     'base_url' => '/foo/',
	 *     'base_url' => 'http://foo.com/'
	 *
	 * Set this to null to have it automatically detected.
	 */
	'base_url'  => null,

    /**
	 * Localization & internationalization settings
	 */
	'language'           => 'en', // Default language
	'language_fallback'  => 'en', // Fallback language when file isn't available for default language
	'locale'             => 'en_US', // PHP set_locale() setting, null to not set


    /**
	 * Internal string encoding charset
	 */
	'encoding'  => 'UTF-8',

    /**
	 * DateTime settings
	 *
	 * server_gmt_offset	in seconds the server offset from gmt timestamp when time() is used
	 * default_timezone		optional, if you want to change the server's default timezone
	 */
	'server_gmt_offset'  => 0,
	'default_timezone'   => null,

     /**
	 * Controller class prefix
	 */
	 'controller_prefix' => 'Controller_',

	 /**
	 * To enable you to split up your additions to the framework, packages are
	 * used. You can define the basepaths for your packages here. By default
	 * empty, but to use them you can add something like this:
	 *      array(APPPATH.'modules'.DS)
	 *
	 * Paths MUST end with a directory separator (the DS constant)!
	 */
	'package_paths' => [
		//PKGPATH
	],
];
