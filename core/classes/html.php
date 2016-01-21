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
 * HTML class
 *
 * The class is used to aid the template clas. It allows the use of a few standard
 * functions for creating links, adding javascript and css.
 *
 * @package		BHive
 * @subpackage	Core
 */
class HTML
{

    /**
     * Includes a js file
     * @param string $fileName
     * @param boolean $cache If set to false, caching will be prevented
     * @return string
     */
	public function includeJs($fileName, $cache = false)
    {
        $data = '<script src="assets/js/' . $fileName . '.js';
        if ( ! $cache) {
            $data .= '?' . time();
        }
        $data .= '"></script>';

        echo $data;
        return $data;
	}

    /**
     * Includes a css file
     * @param string $fileName
     * @param boolean $cache If set to false, caching will be prevented
     * @return string
     */
	public function includeCss($fileName, $cache = false)
    {
        $data = '<link type="text/css" rel="stylesheet" href="assets/css/' . $fileName . '.css';
        if ( ! $cache) {
            $data .= '?' . time();
        }
        $data .= '" />';
        echo $data;
        return $data;
	}

    /**
     * Returns an icon span
	 * TODO: integrate
     * @param string $iconName
     * @return string
     */
    public function icon($iconName)
    {
        $data = '<span class="octicon ' . $iconName . '"></span>';
        return $data;
    }

}
