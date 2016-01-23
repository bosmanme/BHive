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
 * The Asset class will take care of assets (such as css files)
 *
 * @package		BHive
 * @subpackage	Core
 */
class Asset
{

    /**
	 * All the Asset instances
	 *
	 * @var  array
	 */
    protected static $_instances = [
        'css' => [
            // 'main' => 'assets/css/main.css',
        ],
    ];

    /**
	 * Default configuration values
	 *
	 * @var  array
	 */
	protected static $_config = [
		'paths'   => ['assets/'],
		'img_dir' => 'img/',
		'js_dir'  => 'js/',
		'css_dir' => 'css/',

        // Custom folders for each type
		'folders' => [
			'css' => [],
			'js'  => [],
			'img' => [],
		],
		'add_mtime' => true,
        'url'       => '',
	];

    /**
	 * This is called automatically by the Autoloader.  It loads in the config
	 *
	 * @return  void
	 */
	public static function _init()
	{
        // Setup configuration
		Config::load('asset', true);
        static::$_config['paths'] = Config::get('paths', static::$_config['paths']);
        static::$_config['img_dir'] = Config::get('img_dir', static::$_config['img_dir']);
        static::$_config['js_dir'] = Config::get('js_dir', static::$_config['js_dir']);
        static::$_config['css_dir'] = Config::get('css_dir', static::$_config['css_dir']);
        static::$_config['folders'] = Config::get('folders', static::$_config['folders']);
        static::$_config['add_mtime'] = Config::get('add_mtime', static::$_config['add_mtime']);
        static::$_config['url'] = Config::get('url', static::$_config['url']);
	}

    /**
     * Try to return the path for a certain file
     *
     * @param   string  $type   The type of the file (css, img, js,...)
     * @param   string  $file   The name of the file
     * @return  string|null
     */
    protected static function _getPath($file, $type)
    {
        // Did we load this instance alreaydy?
        if ($path = static::_isLoaded($file, $type)) {
            return $path.(static::$_config['add_mtime'] ? '?'.filemtime($path) : '');
        }

        $searchFolders = [];

        // Top level config set?
        $key = $type . '_dir';
        if (array_key_exists($key, static::$_config)) {
            $searchFolders[] = static::$_config[$key];
        }

        // Custom folder set?
        if (array_key_exists('folders', static::$_config)) {
            if (array_key_exists($type, static::$_config['folders'])) {
                $searchFolders = array_merge($searchFolders, static::$_config['folders'][$type]);
            }
        }

        if ( ! empty($searchFolders)) {
            // loop over all asset folders in the config
            foreach (static::$_config['paths'] as $assetPath) {

                // Loop over the type folders
                foreach ($searchFolders as $folder) {
                    $fullPath = static::$_config['url'] . $assetPath . $folder . $file;
                    if (is_file($fullPath)) {

                        // Save the instance
                        static::$_instances[$type][$file] = $fullPath;

                        return $fullPath.(static::$_config['add_mtime'] ? '?'.filemtime($fullPath) : '');
                    }
                }
            }
        }

        return null;
    }

    /**
     * Checks if this file is already loaded in our instances
     * @param   string  $type   The type of the file (css, img, js,...)
     * @param   string  $file   The name of the file
     * @return  string|boolean
     */
    protected static function _isLoaded($file, $type)
    {
        if (array_key_exists($type, static::$_instances)) {
            if (array_key_exists($file, static::$_instances[$type])) {
                return static::$_instances[$type][$file];
            }
        }

        return false;
    }

    /**
     * Returns the path for the given Javascript file
     * @param   $file   The file name
     * @return  string  The path
     */
    public static function js($file)
    {
        // Check extension
        if ( ! File::hasExtension($file, 'js')) {
            $file .= '.js';
        }

        return static::_getPath($file, 'js');
    }

    /**
     * Returns the path for the given CSS file
     * @param   $file   The file name
     * @return  string  The path
     */
    public static function css($file)
    {
        // Check extension
        if ( ! File::hasExtension($file, 'css')) {
            $file .= '.css';
        }

        return static::_getPath($file, 'css');
    }

    /**
     * Returns the path for the given image file
     * Compared to the other methods this will retrn null if the image has no extension!
     *
     * @param   $file   The file name
     * @return  string|null  The path or null if the image has no extension
     */
    public static function img($file)
    {
        // Check extension
        if ( ! File::hasExtension($file, ['png', 'gif', 'jpg', 'jpeg'])) {
            return null;
        }

        return static::_getPath($file, 'css');
    }
}
