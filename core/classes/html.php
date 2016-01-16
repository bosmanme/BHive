<?php
/**
 * HTML class
 *
 * The class is used to aid the template clas. It allows the use of a few standard
 * functions for creating links, adding javascript and css.
 *
 */
class HTML
{

	public static $doctypes = null;
	public static $html5 = true;

    /**
     * Escapes a string
     * @param string $data
     * @return string
     */
	public function sanitize($data)
    {
        return mysql_real_escape_string($data);
	}

    /**
     * Includes a js file
     * @param string $fileName
     * @param boolean $cache If set to false, caching will be prevented
     * @return string
     */
	public function includeJs($fileName, $cache = false)
    {
        $data = '<script src="' . BASEPATH . FOLDER_JS . DS . $fileName . '.js';
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
        $data = '<link type="text/css" rel="stylesheet" href="' . BASEPATH . FOLDER_CSS . DS . $fileName . '.css';
        if ( ! $cache) {
            $data .= '?' . time();
        }
        $data .= '" />';
        echo $data;
        return $data;
	}

    /**
     * Returns an icon span
     * @param string $iconName
     * @return string
     */
    public function icon($iconName)
    {
        $data = '<span class="octicon ' . $iconName . '"></span>';
        return $data;
    }

    /**
     * Returns the path relative to the file, or an absolute path
     * @param string $path
     * @param boolean $absolute
     * @return string
     */
    public function url($page)
    {
        return BASEPATH . $page;
    }

    /**
     * Returns the current server request url
     * @return string
     */
    public function path()
    {
        return $_SERVER['REQUEST_URI'];
    }
}
