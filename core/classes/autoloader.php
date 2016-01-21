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
* The Autoloader is responsible for all class loading.  It allows you to define
* different load paths based on namespaces.  It also lets you set explicit paths
* for classes to be loaded from.
 *
 * @package		BHive
 * @subpackage	Core
 */
class Autoloader
{
    /**
	 * @var  array  $classes  holds all the classes and paths
	 */
	protected static $classes = [];

	/**
	 * @var array saves all namespaces
	 */
	protected static $namespaces = [];

	/**
	 * Holds all the PSR-0 compliant namespaces.  These namespaces should
	 * be loaded according to the PSR-0 standard.
	 *
	 * @var  array
	 */
	protected static $psrNamespaces = array();

	/**
	 * @var  array  list off namespaces of which classes will be aliased to global namespace
	 */
	protected static $coreNamespaces = array(
		'BHive\\Core',
	);

	/**
	 * @var  bool  whether to initialize a loaded class
	 */
	protected static $autoInitialize = null;

	/**
	 * Adds a namespace search path.  Any class in the given namespace will be
	 * looked for in the given path.
	 *
	 * @param   string  $namespace  the namespace
	 * @param   string  $path       the path
	 * @param   bool    $psr        whether this is a PSR-0 compliant class
	 * @return  void
	 */
	public static function addNamespace($namespace, $path, $psr = false)
	{
		static::$namespaces[$namespace] = $path;
		if ($psr) {
			static::$psrNamespaces[$namespace] = $path;
		}
	}

	/**
	 * Adds an array of namespace paths. See {@see Autoloader::addNamespace}.
	 *
	 * @param   array  $namespaces  the namespaces
	 * @param   bool   $prepend     whether to prepend the namespace to the search path
	 * @return  void
	 */
	public static function addNamespaces(array $namespaces, $prepend = false)
	{
		if ( ! $prepend) {
			static::$namespaces = array_merge(static::$namespaces, $namespaces);
		}
		else {
			static::$namespaces = $namespaces + static::$namespaces;
		}
	}

	/**
	 * Returns the namespace's path or false when it doesn't exist.
	 *
	 * @param   string      the namespace to get the path for
	 * @return  array|bool  the namespace path or false
	 */
	public static function namespacePath($namespace)
	{
		if ( ! array_key_exists($namespace, static::$namespaces))
		{
			return false;
		}

		return static::$namespaces[$namespace];
	}

	/**
	 * Adds a classes load path.  Any class added here will not be searched for
	 * but explicitly loaded from the path.
	 *
	 * @param   string  the class name
	 * @param   string  the path to the class file
	 * @return  void
	 */
	public static function addClass($class, $path)
	{
		static::$classes[strtolower($class)] = $path;
	}

	/**
	 * Adds multiple class paths to the load path. See {@see Autoloader::addClass}.
	 *
	 * @param   array  $classes  the class names and paths
	 * @return  void
	 */
	public static function addClasses($classes)
	{
		foreach ($classes as $class => $path) {
			static::$classes[strtolower($class)] = $path;
		}
	}

	/**
	 * Aliases the given class into the given Namespace.  By default it will
	 * add it to the global namespace.
	 *
	 * <code>
	 * Autoloader::aliasToNamespace('Foo\\Bar');
	 * Autoloader::aliasToNamespace('Foo\\Bar', '\\Baz');
	 * </code>
	 *
	 * @param  string  $class      the class name
	 * @param  string  $namespace  the namespace to alias to
	 */
	public static function aliasToNamespace($class, $namepsace = '')
	{
		empty($namespace) or $namespace = rtrim($namespace, '\\').'\\';
		$parts = explode('\\', $class);
		$rootClass = $namespace . array_pop($parts);
		class_alias($class, $rootClass);
	}

	/**
	 * Returns the class with namespace prefix when available
	 *
	 * @param   string       $class
	 * @return  bool|string
	 */
	protected static function findCoreClass($class)
	{
		foreach (static::$coreNamespaces as $ns) {
			if (array_key_exists(
					strtolower($nsClass = $ns.'\\'.$class),
					static::$classes)
			) {
				return $nsClass;
			}
		}

		return false;
	}

	/**
	 * Add a namespace for which classes may be used without the namespace prefix and
	 * will be auto-aliased to the global namespace.
	 * Prefixing the classes will overwrite core classes and previously added namespaces.
	 *
	 * @param  string $namespace
	 * @param  bool   $prefix
	 * @return void
	 */
	public static function addCoreNamespace($namespace, $prefix = true)
	{
		if ($prefix) {
			array_unshift(static::$coreNamespaces, $namespace);
		}
		else {
			static::$coreNamespaces[] = $namespace;
		}
	}

    /**
	 * Loads a class.
	 *
	 * @param   string  $class  Class to load
	 * @return  bool    If it loaded the class
	 */
    public static function load($class)
    {
		// deal with funny is_callable('static::classname') side-effect
		if (strpos($class, 'static::') === 0) {
			// is called from within the class, so it's already loaded
			return true;
		}

		$loaded = false;

        // Trim the classname
        $class = ltrim($class, '\\');
		$pos = strripos($class, '\\');

		// Check if the class is already present
		if (isset(static::$classes[strtolower($class)])) {
			static::initClass($class, str_replace('/', DS, static::$classes[strtolower($class)]));
			$loaded = true;
		} elseif ($fullClass = static::findCoreClass($class)) {
			if ( ! class_exists($fullClass, false)) {
				include static::prepPath(static::$classes[strtolower($fullClass)]);
			}

			if ( ! class_exists($class, false)) {
				class_alias($fullClass, $class);
			}

			static::initClass($class);
			$loaded = true;
		} else {
			$fullNs = substr($class, 0, $pos);

			if ($fullNs) {
				foreach (static::$namespaces as $ns => $path) {
					$ns = ltrim($ns, '\\');
					if (stripos($fullNs, $ns) === 0) {
						$path .= static::classToPath(
							substr($class, strlen($ns) + 1),
							array_key_exists($ns, static::$psrNamespaces)
						);

						if (is_file($path)) {
							static::initClass($class, $path);
							$loaded = true;
							break;
						}
					}
				}
			}
		}


		if ( ! $loaded) {
			$path = APPPATH . 'classes' . DS . static::classToPath($class);
			if (is_file($path)) {
				static::initClass($class, $path);
				$loaded = true;
				static::addClass($class, $path);
			}
		}

    }

	/**
	 * Prepares a given path by making sure the directory separators are correct.
	 *
	 * @param   string  $path  Path to prepare
	 * @return  string  Prepped path
	 */
	protected static function prepPath($path)
	{
		return str_replace(array('/', '\\'), DS, $path);
	}

	/**
	 * Takes a class name and turns it into a path. By replacing _ with a DS
	 *
	 * @param   string  $class  Class name
	 * @param   bool    $psr    Whether this is a PSR-0 compliant class
	 * @return  string  Path for the class
	 */
	protected static function classToPath($class, $psr = false)
	{
		$file  = '';

		if ($lastNsPos = strripos($class, '\\')) {
			$namespace = substr($class, 0, $lastNsPos);
			$class = substr($class, $lastNsPos + 1);
			$file = str_replace('\\', DS, $namespace) . DS;
		}

		$file .= str_replace('_', DS, $class).'.php';

		if ( ! $psr) {
			$file = strtolower($file);
	    }

		return $file;
	}

	/**
	 * Checks to see if the given class has a static _init() method.  If so then
	 * it calls it.
	 *
	 * @param string $class the class name
	 * @param string $file  the file containing the class to include
	 */
	protected static function initClass($class, $file = null)
	{

		// include the file if needed
		if ($file)
		{
			include $file;
		}

		// if the loaded file contains a class...
		if (class_exists($class, false))
		{
			// call the classes static init if needed
			if (static::$autoInitialize === $class)
			{
				static::$autoInitialize = null;
				if (method_exists($class, '_init') and is_callable($class.'::_init'))
				{
					call_user_func($class.'::_init');
				}
			}
		}
	}

    /**
	 * Register's the autoloader to the SPL autoload stack.
	 *
	 * @return	void
	 */
    public static function register()
    {
        spl_autoload_register('Autoloader::load', true, true);
    }
}
