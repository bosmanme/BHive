<?php
/**
 * The Autoloader is responsible for all class loading.  It allows you to define
 * different load paths based on namespaces.  It also lets you set explicit paths
 * for classes to be loaded from.
 * @version    1.0
 * @author     Mathias Bosman
 * @license    MIT License
 * @copyright  2016 Mathias Bosman
 * @package     Bhive
 * @subpackage  Core
 */
class Autoloader
{
    /**
	 * @var  array  $classes  holds all the classes and paths
	 */
	protected static $classes = [];

	/**
	 * @var  bool  whether to initialize a loaded class
	 */
	protected static $autoInitialize = null;

	/**
	 * Adds a classes load path.  Any class added here will not be searched for
	 * but explicitly loaded from the path.
	 *
	 * @param   string  the class name
	 * @param   string  the path to the class file
	 * @return  void
	 */
	public static function add_class($class, $path)
	{
		static::$classes[strtolower($class)] = $path;
	}

	/**
	 * Adds multiple class paths to the load path. See {@see Autoloader::add_class}.
	 *
	 * @param   array  $classes  the class names and paths
	 * @return  void
	 */
	public static function add_classes($classes)
	{
		foreach ($classes as $class => $path)
		{
			static::$classes[strtolower($class)] = $path;
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
        // Trim the classname
        $class = trim($class);
		$loaded = false;

		// Check if the class is already present
		if (isset(static::$classes[strtolower($class)])) {
			static::init_class($class, str_replace('/', DS, static::$classes[strtolower($class)]));
			$loaded = true;
		} else {
			$path = APPPATH . static::class_to_path($class);
			if (is_file($path)) {
				static::init_class($class, $path);
				$loaded = true;
			}
		}

		// If still not loaded check for model loading
		$path = APPPATH . 'model' . DS . static::class_to_path($class);
		if (is_file($path)) {
			static::init_class($class, $path);
		}
    }

	/**
	 * Takes a class name and turns it into a path. By replacing _ with a DS
	 *
	 * @param   string  $class  Class name
	 * @return  string  Path for the class
	 */
	protected static function class_to_path($class)
	{
		$file  = '';
		$file .= str_replace('_', DS, $class).'.php';
		$file = strtolower($file);

		return $file;
	}

	/**
	 * Checks to see if the given class has a static _init() method.  If so then
	 * it calls it.
	 *
	 * @param string $class the class name
	 * @param string $file  the file containing the class to include
	 */
	protected static function init_class($class, $file = null)
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
