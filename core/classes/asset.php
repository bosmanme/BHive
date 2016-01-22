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
    protected static $_instances = [];

    /**
	 * Default configuration values
	 *
	 * @var  array
	 */
	protected static $_config = array(
		'paths'   => ['assets/'],
		'img_dir' => 'img/',
		'js_dir'  => 'js/',
		'css_dir' => 'css/',
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
	);

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

    protected static function _load($type, $file)
    {

    }

    public static function css($file)
    {
        $file = $file . '.css';
    }
}
