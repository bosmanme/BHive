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

namespace BHive\Core;

/**
 * The abstract Controller class
 *
 * @package		BHive
 * @subpackage	Core
 */
class Cookie
{
    /**
	 * @var  array  Cookie class configuration defaults
	 */
	protected static $config = [
		'expiration'            => 0,
		'path'                  => '/',
		'domain'                => null,
		'secure'                => false,
		'http_only'             => false,
	];

    /*
	 * initialisation and auto configuration
	 */
	public static function _init()
	{
		static::$config = array_merge(static::$config, Config::get('cookie', []));
	}

    /**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "color" cookie, or use "blue" if the cookie does not exist
	 *     $theme = Cookie::get('color', 'blue');
	 *
	 * @param   string  cookie name
	 * @param   mixed   default value to return
	 * @return  string
	 */
	public static function get($name = null, $default = null)
	{
		return Input::cookie($name, $default);
	}

    /**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 *     // Set the "var" cookie
	 *     Cookie::set('var', 'red');
	 *
	 * @param   string    name of cookie
	 * @param   string    value of cookie
	 * @param   integer   lifetime in seconds
	 * @param   string    path of the cookie
	 * @param   string    domain of the cookie
	 * @param   boolean   if true, the cookie should only be transmitted over a secure HTTPS connection
	 * @param   boolean   if true, the cookie will be made accessible only through the HTTP protocol
	 * @return  boolean
	 */
    public static function set($name, $value, $expiration = null, $path = null, $domain = null, $secure = null, $http_only = null)
    {
        // use the class defaults for the other parameters if not provided
		is_null($expiration) and $expiration = static::$config['expiration'];
		is_null($path) and $path = static::$config['path'];
		is_null($domain) and $domain = static::$config['domain'];
		is_null($secure) and $secure = static::$config['secure'];
		is_null($http_only) and $http_only = static::$config['http_only'];

		// add the current time so we have an offset
		$expiration = $expiration > 0 ? $expiration + time() : 0;

		return setcookie($name, $value, $expiration, $path, $domain, $secure, $http_only);
    }

    /**
	 * Deletes a cookie by making the value null and expiring it.
	 *
	 *     Cookie::delete('var');
	 *
	 * @param   string   cookie name
 	 * @param   string    path of the cookie
	 * @param   string    domain of the cookie
	 * @param   boolean   if true, the cookie should only be transmitted over a secure HTTPS connection
	 * @param   boolean   if true, the cookie will be made accessible only through the HTTP protocol
	 * @return  boolean
	 * @uses    static::set
	 */
	public static function delete($name, $path = null, $domain = null, $secure = null, $http_only = null)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return static::set($name, null, -86400, $path, $domain, $secure, $http_only);
	}
}
