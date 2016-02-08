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
* Crypt can be used to encrypt and decrypt data
 *
 * @package		BHive
 * @subpackage	Core
 */
class Crypt
{
    /**
     * Crypt default configuration
     * @var array
     */
    protected static $_config = [
        'cost' => 9,
    ];

    protected static $_method = PASSWORD_DEFAULT;

    public static function init(){}

    /**
     * Hash a string with set configuration
     * @param  string $string The string to hash
     * @return string         The hashed string
     */
    public static function hash($string)
    {
        return password_hash($string, static::$_method, static::$_config);
    }

    /**
     * Verify a passowrd
     * @param  string $password password to check against a hash
     * @param  string $hash     The hash to compare too
     * @return bool
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Benchmark the server to determine how high of cost you can afford.
     * You want to set the highest cost that you can with slowing down the server
     * too much. 8-10 is a good baseline and more is good if your servers are
     * fast enough. The code below aims for a default <= 50 millieseconds stretching
     * time, which is a good baseline for systems handling interactive logins.
     *
     * @param  float  $timeTarget Timetarget in seconds
     * @return int
     */
    public static function benchmarkCost($timeTarget = 0.05)
    {
        $cost = 8;
        do {
            $cost ++;
            $start = microtime(true);

            password_hash("test", static::$_method, static::$_config);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        return $cost;
    }
}
