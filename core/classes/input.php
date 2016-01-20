<?php
/**
 * Input class
 *
 * The input class allows you to access HTTP parameters, load server variables
 * and user agent details.
 *
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
}
