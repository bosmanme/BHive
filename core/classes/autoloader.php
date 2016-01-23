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
	protected static $_classes = [];

	/**
	 * @var array saves all namespaces
	 */
	protected static $_namespaces = [];

	/**
	 * Holds all the PSR-0 compliant namespaces.  These namespaces should
	 * be loaded according to the PSR-0 standard.
	 *
	 * @var  array
	 */
	protected static $_psrNamespaces = array();

	/**
	 * @var  array  list off namespaces of which classes will be aliased to global namespace
	 */
	protected static $_coreNamespaces = array(
		'BHive\\Core',
	);

	/**
	 * @var  boolean  whether to initialize a loaded class
	 */
	protected static $_autoInitialize = null;

	/**
	 * Adds a namespace search path.  Any class in the given namespace will be
	 * looked for in the given path.
	 *
	 * @param   string  $namespace  the namespace
	 * @param   string  $path       the path
	 * @param   boolean    $psr        whether this is a PSR-0 compliant class
	 * @return  void
	 */
	public static function addNamespace($namespace, $path, $psr = false)
	{
		static::$_namespaces[$namespace] = $path;
		if ($psr) {
			static::$_psrNamespaces[$namespace] = $path;
		}
	}

	/**
	 * Adds an array of namespace paths. See {@see Autoloader::addNamespace}.
	 *
	 * @param   array  $namespaces  the namespaces
	 * @param   boolean   $prepend     whether to prepend the namespace to the search path
	 * @return  void
	 */
	public static function addNamespaces(array $namespaces, $prepend = false)
	{
		if ( ! $prepend) {
			static::$_namespaces = array_merge(static::$_namespaces, $namespaces);
		}
		else {
			static::$_namespaces = $namespaces + static::$_namespaces;
		}
	}

	/**
	 * Returns the namespace's path or false when it doesn't exist.
	 *
	 * @param   string      the namespace to get the path for
	 * @return  array|boolean  the namespace path or false
	 */
	public static function namespacePath($namespace)
	{
		if ( ! array_key_exists($namespace, static::$_namespaces))
		{
			return false;
		}

		return static::$_namespaces[$namespace];
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
		static::$_classes[strtolower($class)] = $path;
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
			static::$_classes[strtolower($class)] = $path;
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
	 * @return  boolean|string
	 */
	protected static function _findCoreClass($class)
	{
		foreach (static::$_coreNamespaces as $ns) {
			if (array_key_exists(
					strtolower($nsClass = $ns.'\\'.$class),
					static::$_classes)
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
	 * @param  boolean   $prefix
	 * @return void
	 */
	public static function addCoreNamespace($namespace, $prefix = true)
	{
		if ($prefix) {
			array_unshift(static::$_coreNamespaces, $namespace);
		}
		else {
			static::$_coreNamespaces[] = $namespace;
		}
	}

    /**
	 * Loads a class.
	 *
	 * @param   string  $class  Class to load
	 * @return  boolean    If it loaded the class
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

		//Iinitialize the class
		if (empty(static::$_autoInitialize)) {
			static::$_autoInitialize = $class;
		}

		// Check if the class is already present
		if (isset(static::$_classes[strtolower($class)])) {
			static::_initClass($class, str_replace('/', DS, static::$_classes[strtolower($class)]));
			$loaded = true;
		} elseif ($fullClass = static::_findCoreClass($class)) {
			if ( ! class_exists($fullClass, false)) {
				include static::_prepPath(static::$_classes[strtolower($fullClass)]);
			}

			if ( ! class_exists($class, false)) {
				class_alias($fullClass, $class);
			}

			static::_initClass($class);
			$loaded = true;
		} else {
			$fullNs = substr($class, 0, $pos);

			if ($fullNs) {
				foreach (static::$_namespaces as $ns => $path) {
					$ns = ltrim($ns, '\\');
					if (stripos($fullNs, $ns) === 0) {
						$path .= static::_classToPath(
							substr($class, strlen($ns) + 1),
							array_key_exists($ns, static::$_psrNamespaces)
						);

						if (is_file($path)) {
							static::_initClass($class, $path);
							$loaded = true;
							break;
						}
					}
				}
			}
		}


		if ( ! $loaded) {
			$path = APPPATH . 'classes' . DS . static::_classToPath($class);
			if (is_file($path)) {
				static::_initClass($class, $path);
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
	protected static function _prepPath($path)
	{
		return str_replace(array('/', '\\'), DS, $path);
	}

	/**
	 * Takes a class name and turns it into a path. By replacing _ with a DS
	 *
	 * @param   string  $class  Class name
	 * @param   boolean    $psr    Whether this is a PSR-0 compliant class
	 * @return  string  Path for the class
	 */
	protected static function _classToPath($class, $psr = false)
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
	protected static function _initClass($class, $file = null)
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
			if (static::$_autoInitialize === $class)
			{
				static::$_autoInitialize = null;
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
