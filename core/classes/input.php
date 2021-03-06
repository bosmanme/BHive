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
 * Input class
 *
 * The input class allows you to access HTTP parameters, load server variables
 * and user agent details.
 *
 * @package		BHive
 * @subpackage	Core
 */
class Input
{

    /**
	 * Fetch an item from the SERVER array
	 *
	 * @param   string  The index key
	 * @param   mixed   The default value
	 * @return  string|array
	 */
	public static function server($index = null, $default = null)
	{
		return (func_num_args() === 0) ? $_SERVER : Arr::get($_SERVER, strtoupper($index), $default);
	}

    /**
	 * Fetch an item from the COOKIE array
	 *
	 * @param    string  The index key
	 * @param    mixed   The default value
	 * @return   string|array
	 */
	public static function cookie($index = null, $default = null)
	{
		return (func_num_args() === 0) ? $_COOKIE : Arr::get($_COOKIE, $index, $default);
	}

    /**
	 * Get the public ip address of the user.
	 *
	 * @return  string
	 */
	public static function ip($default = '0.0.0.0')
	{
		return static::server('REMOTE_ADDR', $default);
	}

    /**
	 * Return's the query string
	 *
	 * @return  string
	 */
	public static function queryString($default = '')
	{
		return static::server('QUERY_STRING', $default);
	}

    /**
	 * Fetch a item from the HTTP request headers
	 *
	 * @return  array
	 */
	public static function headers($index = null, $default = null)
	{
		static $headers = null;

		// do we need to fetch the headers?
		if ($headers === null)
		{
			// deal with fcgi or nginx installs
			if ( ! function_exists('getallheaders'))
			{
				$server = Arr::filterPrefixed(static::server(), 'HTTP_', true);

				foreach ($server as $key => $value)
				{
					$key = join('-', array_map('ucfirst', explode('_', strtolower($key))));

					$headers[$key] = $value;
				}

				$value = static::server('Content_Type', static::server('Content-Type')) and $headers['Content-Type'] = $value;
				$value = static::server('Content_Length', static::server('Content-Length')) and $headers['Content-Length'] = $value;
			}
			else
			{
				$headers = getallheaders();
			}
		}

		return empty($headers) ? $default : ((func_num_args() === 0) ? $headers : Arr::get(array_change_key_case($headers), strtolower($index), $default));
	}
}
