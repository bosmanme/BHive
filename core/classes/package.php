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

	/**
	 * Unloads a package from the stack.
	 *
	 * @param   string  $pacakge  The package name
	 * @return  void
	 */
	public static function unload($package)
	{
		unset(static::$packages[$package]);
	}

	/**
	 * Checks if the given package is loaded, if no package is given then
	 * all loaded packages are returned.
	 *
	 * @param   string|null  $package  The package name or null
	 * @return  bool|array  Whether the package is loaded, or all packages
	 */
	public static function loaded($package = null)
	{
		if ($package === null)
		{
			return static::$packages;
		}

		return array_key_exists($package, static::$packages);
	}

	/**
	 * Checks if the given package exists.
	 *
	 * @param   string  $package  The package name
	 * @return  bool|string  Path to the package found, or false if not found
	 */
	public static function exists($package)
	{
		if (array_key_exists($package, static::$packages))
		{
			return static::$packages[$package];
		}
		else
		{
			$paths = Config::get('package_paths', array());
			empty($paths) and $paths = array(PKGPATH);
			$package = strtolower($package);

			foreach ($paths as $path)
			{
				if (is_dir($path.$package))
				{
					return $path . $package.DS;
				}
			}
		}

		return false;
	}
}
