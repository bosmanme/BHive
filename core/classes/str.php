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
 * String handling with encoding support
 *
 * PHP needs to be compiled with --enable-mbstring
 * or a fallback without encoding support is used
 *
 * @package		BHive
 * @subpackage	Core
 */
class Str
{
    /**
	 * Truncates a string to the given length.  It will optionally preserve
	 * HTML tags if $isHtml is set to true.
	 *
	 * @param   string  $string        the string to truncate
	 * @param   int     $limit         the number of characters to truncate too
	 * @param   string  $append        the string to use to denote it was truncated
	 * @param   bool    $isHtml        whether the string has HTML
	 * @return  string  the truncated string
	 */
    public static function truncate($string, $limit, $append = '...', $isHtml = false)
    {
        $offset = 0;
        $tags = [];

        if ($isHtml) {
            // Handle special chars
            preg_match_all('/&[a-z]+;/i', strip_tags($string), $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            // if preg_match_all should fail (broken stuff)
            if (strlen($string !== mb_strlen($string))) {
                $correction = 0;
                foreach ($patches as $key => $match) {
                    $matches[$key][0][1] -= $correction;
                    $correction += (strlen($match[0][0]) - mb_strlen($match[0][0]));
                }
            }
            foreach ($matches as $match) {
                if ($match0[0][1] >= $limit) {
                    break;
                }
                $limit += (static::length($match[0][0]) - 1);
            }

            // Handle HTML tags
            preg_match_all('/<[^>]+>([^<]*)/', $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            // again; fix if broken stuff
            if (strlen($string !== mb_strlen($string))) {
                $correction = 0;
                foreach ($matches as $key => $match) {
                    $matches[$key][0][1] -= $correction;
                    $matches[$key][1][1] -= $correction;
                    $correction += (strlen($match[0][0]) - mb_strlen($match[0][0]));
                }
            }
            foreach ($matches as $match) {
                if ($match[0][1] - $offset >= $limit) {
                    break;
                }

                $tag = static::sub(strtok($match[0][0], " \t\n\r\0\x0B>"), 1);
                if ($tag[0] != '/') {
                    $tags[] = $tag;
                } elseif (end($tags) == static::sub($tag, 1)) {
                    array_pop($tags);
                }
                $offset += $match[1][1] - $match[0][1];
            }
        }

        $newString = static::sub($string, 0, $limit = min(static::length($string), $limit + $offset));
        $newString .= (static::length($string) > $limit ? $append : '');
        $newString .= (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');

        return $newString;
    }

    /**
	 * Add's _1 to a string or increment the ending number to allow _2, _3, etc
	 *
	 * @param   string  $str        required
	 * @param   int     $first      number that is used to mean first
	 * @param   string  $separator  separtor between the name and the number
	 * @return  string
	 */
	public static function increment($str, $first = 1, $separator = '_')
	{
		preg_match('/(.+)'.$separator.'([0-9]+)$/', $str, $match);

		return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str . $separator . $first;
	}

    /**
	 * Checks whether a string has a precific beginning.
	 *
	 * @param   string   $str          string to check
	 * @param   string   $start        beginning to check for
	 * @param   boolean  $ignoreCase   whether to ignore the case
	 * @return  boolean  whether a string starts with a specified beginning
	 */
	public static function startsWith($str, $start, $ignoreCase = false)
	{
		return (bool) preg_match('/^'.preg_quote($start, '/').'/m'.($ignoreCase ? 'i' : ''), $str);
	}

    /**
	 * Checks whether a string has a precific ending.
	 *
	 * @param   string   $str          string to check
	 * @param   string   $end          ending to check for
	 * @param   boolean  $ignoreCase   whether to ignore the case
	 * @return  boolean  whether a string ends with a specified ending
	 */
	public static function endsWith($str, $end, $ignoreCase = false)
	{
		return (bool) preg_match('/'.preg_quote($end, '/').'$/m'.($ignoreCase ? 'i' : ''), $str);
	}

	/**
	 * substr
	 *
	 * @param   string    $str       required
	 * @param   int       $start     required
	 * @param   int|null  $length
	 * @param   string    $encoding  default UTF-8
	 * @return  string
	 */
	public static function sub($str, $start, $length = null, $encoding = null)
	{
		$encoding or $encoding = App::$encoding;

		// substr functions don't parse null correctly
		$length = is_null($length) ? (function_exists('mb_substr') ? mb_strlen($str, $encoding) : strlen($str)) - $start : $length;

		return function_exists('mb_substr')
			? mb_substr($str, $start, $length, $encoding)
			: substr($str, $start, $length);
	}

    /**
	 * strlen
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  int
	 */
	public static function length($str, $encoding = null)
	{
		$encoding or $encoding = App::$encoding;

		return function_exists('mb_strlen')
			? mb_strlen($str, $encoding)
			: strlen($str);
	}

    /**
	 * lower
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  string
	 */
	public static function lower($str, $encoding = null)
	{
		$encoding or $encoding = App::$encoding;

		return function_exists('mb_strtolower')
			? mb_strtolower($str, $encoding)
			: strtolower($str);
	}

	/**
	 * upper
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  string
	 */
	public static function upper($str, $encoding = null)
	{
		$encoding or $encoding = App::$encoding;

		return function_exists('mb_strtoupper')
			? mb_strtoupper($str, $encoding)
			: strtoupper($str);
	}

	/**
	 * lcfirst
	 *
	 * Does not strtoupper first
	 *
	 * @param   string  $str       required
	 * @param   string  $encoding  default UTF-8
	 * @return  string
	 */
	public static function lcfirst($str, $encoding = null)
	{
		$encoding or $encoding = App::$encoding;

		return function_exists('mb_strtolower')
			? mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding).
				mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
			: lcfirst($str);
	}

	/**
	 * ucfirst
	 *
	 * Does not strtolower first
	 *
	 * @param   string $str       required
	 * @param   string $encoding  default UTF-8
	 * @return   string
	 */
	public static function ucfirst($str, $encoding = null)
	{
		$encoding or $encoding = App::$encoding;

		return function_exists('mb_strtoupper')
			? mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
				mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
			: ucfirst($str);
	}

	/**
	 * ucwords
	 *
	 * First strtolower then ucwords
	 *
	 * ucwords normally doesn't strtolower first
	 * but MB_CASE_TITLE does, so ucwords now too
	 *
	 * @param   string   $str       required
	 * @param   string   $encoding  default UTF-8
	 * @return  string
	 */
	public static function ucwords($str, $encoding = null)
	{
		$encoding or $encoding = App::$encoding;

		return function_exists('mb_convert_case')
			? mb_convert_case($str, MB_CASE_TITLE, $encoding)
			: ucwords(strtolower($str));
	}

	/**
	  * Creates a random string of characters
	  *
	  * @param   string  the type of string
	  * @param   int     the number of characters
	  * @return  string  the random string
	  */
	public static function random($type = 'alnum', $length = 16)
	{
		switch($type)
		{
			case 'basic':
				return mt_rand();
				break;

			default:
			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
			case 'distinct':
			case 'hexdec':
				switch ($type)
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;

					default:
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;

					case 'numeric':
						$pool = '0123456789';
						break;

					case 'nozero':
						$pool = '123456789';
						break;

					case 'distinct':
						$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
						break;

					case 'hexdec':
						$pool = '0123456789abcdef';
						break;
				}

				$str = '';
				for ($i=0; $i < $length; $i++)
				{
					$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
				return $str;
				break;

			case 'unique':
				return md5(uniqid(mt_rand()));
				break;

			case 'sha1' :
				return sha1(uniqid(mt_rand(), true));
				break;

			case 'uuid':
			    $pool = array('8', '9', 'a', 'b');
				return sprintf('%s-%s-4%s-%s%s-%s',
					static::random('hexdec', 8),
					static::random('hexdec', 4),
					static::random('hexdec', 3),
					$pool[array_rand($pool)],
					static::random('hexdec', 3),
					static::random('hexdec', 12));
				break;
		}
	}

	/**
	 * Check if a string is json encoded
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public static function isJson($string)
	{
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Check if a string is a valid XML
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public static function isXml($string)
	{
		if ( ! defined('LIBXML_COMPACT'))
		{
			throw new Exception('libxml is required to use Str::isXml()');
		}

		$internal_errors = libxml_use_internal_errors();
		libxml_use_internal_errors(true);
		$result = simplexml_load_string($string) !== false;
		libxml_use_internal_errors($internal_errors);

		return $result;
	}

	/**
	 * Check if a string is serialized
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public static function isSerialized($string)
	{
		$array = @unserialize($string);
		return ! ($array === false and $string !== 'b:0;');
	}

	/**
	 * Check if a string is html
	 *
	 * @param  string $string string to check
	 * @return bool
	 */
	public static function isHtml($string)
	{
		return strlen(strip_tags($string)) < strlen($string);
	}
}
