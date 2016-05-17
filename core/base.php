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

 /**
  * Create a XHTML tag
  *
  * @param	string			The tag name
  * @param	array|string	The tag attributes
  * @param	string|bool		The content to place in the tag, or false for no closing tag
  * @return	string
  */
 if ( ! function_exists('html_tag')) {
 	function html_tag($tag, $attr = array(), $content = false) {
 		// list of void elements (tags that can not have content)
 		static $voidElements = array(
 			// html4
 			"area","base","br","col","hr","img","input","link","meta","param",
 			// html5
 			"command","embed","keygen","source","track","wbr",
 			// html5.1
 			"menuitem",
 		);

 		// construct the HTML
 		$html = '<' . $tag;
 		$html .= ( ! empty($attr)) ? ' ' . (is_array($attr) ? array_to_attr($attr) : $attr) : '';

 		// a void element?
 		if (in_array(strtolower($tag), $voidElements)) {
 			// these can not have content
 			$html .= ' />';
 		}
 		else {
 			// add the content and close the tag
 			$html .= '>' . $content . '</' . $tag . '>';
 		}

 		return $html;
 	}
 }

 /**
 * Takes an array of attributes and turns it into a string for an html tag
 *
 * @param	array	$attr
 * @return	string
 */
if ( ! function_exists('array_to_attr')) {
	function array_to_attr($attr) {
		$attrStr = '';

		foreach ((array) $attr as $property => $value) {
			// Ignore null/false
			if ($value === null or $value === false) {
				continue;
			}

			// If the key is numeric then it must be something like selected="selected"
			if (is_numeric($property)) {
				$property = $value;
			}

			$attr_str .= $property . '="' . str_replace('"', '&quot;', $value) . '" ';
		}

		// We strip off the last space for return
		return trim($attrStr);
	}
}

 /**
  * Takes a classname and returns the actual classname for an alias or just the classname
  * if it's a normal class.
  *
  * @param   string  classname to check
  * @return  string  real classname
  */
 if ( ! function_exists('get_real_class')) {
 	function get_real_class($class) {
 		static $classes = array();

 		if ( ! array_key_exists($class, $classes)) {
 			$reflect = new ReflectionClass($class);
 			$classes[$class] = $reflect->getName();
 		}

 		return $classes[$class];
 	}
 }
