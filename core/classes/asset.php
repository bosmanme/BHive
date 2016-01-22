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
	 * Default configuration values
	 *
	 * @var  array
	 */
	protected static $default_config = array(
		'paths' => array('assets/'),
		'img_dir' => 'img/',
		'js_dir' => 'js/',
		'css_dir' => 'css/',
		'folders' => [
			'css' => [],
			'js'  => [],
			'img' => [],
		],
		'url' => '/',
		'add_mtime' => true,
		'indent_level' => 1,
		'indent_with' => "\t",
		'auto_render' => true,
		'fail_silently' => false,
	);

    /**
	 * This is called automatically by the Autoloader.  It loads in the config
	 *
	 * @return  void
	 */
	public static function _init()
	{
		\Config::load('asset', true, false, true);
	}

    //TODO Keep it simple? Or create asset instances
}
