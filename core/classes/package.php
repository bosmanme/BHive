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
 * Handles all the loading, unloading and management of packages.
 *
 * @package		BHive
 * @subpackage	Core
 */
class Package
{
    /**
	 * @var  array  $packages  Holds all the loaded package information.
	 */
	protected static $packages = [];

    /**
	 * Loads the given package.  If a path is not given, if will search through
	 * the defined package_paths. If not defined, then PKGPATH is used.
	 * It also accepts an array of packages as the first parameter.
	 *
	 * @param   string|array  $package  The package name or array of packages.
	 * @param   string|null   $path     The path to the package
	 * @return  bool  True on success
	 */
	public static function load($package, $path = null)
	{
        if (is_array($package)) {
            $result = true;

            foreach ($package as $pkg => $path) {
                if (is_numeric($pkg)) {
                    $pkg = $path;
                    $path = null;
                }
                $result = $result and static::load($pkg, $path);
            }
            return $result;
        }

        if (static::loaded($package)) {
            return;
        }

        // if no path given, try locating it
        if ($path === null) {
            $paths = Config::get('package_paths', []);
            empty($paths) and $paths = [PKGPATH];

            if ( ! empty($paths)) {
                foreach ($paths as $modpath) {
                    if (is_dir($path = $modpath.strtolower($package) . DS)) {
                        break;
                    }
                }
            }
        }

        if ( ! is_dir($path)) {
            return false;
        }

        App::load($path . 'bootstrap.php');
        static::$packages[$package] = $path;

        return true;
    }
}
