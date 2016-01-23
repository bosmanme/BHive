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
		'url'             => '/',
		'add_mtime'       => true,
		'indent_level'    => 1,
		'indent_with'     => "\t",
		'auto_render'     => true,
		'fail_silently'   => false,
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

	}

    /**
     * Try to return the path for a certain file
     *
     * @param   string  $type   The type of the file (css, img, js,...)
     * @param   string  $file   The name of the file
     * @return  string
     */
    protected static function _getPath($file, $type)
    {
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
            //TODO finish
        }
    }

    public static function css($file)
    {
        $file = $file . '.css';
    }
}
