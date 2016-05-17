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
 * Debug class
 *
 * The Debug class is a simple utility for debugging variables, objects, arrays, etc by outputting information to the display.
 *
 * @package		BHive
 * @subpackage	Core
 */
class Debug
{

    public static $maxNestingLevel = 5;

    public static $jsToggleOpen = false;

    protected static $_jsDisplayed = false;

    protected static $_files = array();

    public static function dump()
    {
        $backtrace = debug_backtrace();

        // Locate the first file that is not this class'
        foreach ($backtrace as $stack => $trace) {
            if (isset($trace['file'])) {
                // If begin called from within, show the file above
                if (strpos($trace['file'], 'core/classes/debug.php') !== false) {
                    $callee = $backtrace[$stack+1];
                    $label = Inflection::humanize($backtrace[$stack+1]['function']);
                } else {
                    $callee = $trace;
                    $label = 'Debug';
                }
            }

            $callee['file'] = App::cleanPath($callee['file']);

            break;
        }

        $arguments = func_get_args();

        if ( ! static::$_jsDisplayed) {
            echo <<<JS
	<script type="text/javascript">function bhive_debug_toggle(a){if(document.getElementById){if(document.getElementById(a).style.display=="none"){document.getElementById(a).style.display="block"}else{document.getElementById(a).style.display="none"}}else{if(document.layers){if(document.id.display=="none"){document.id.display="block"}else{document.id.display="none"}}else{if(document.all.id.style.display=="none"){document.all.id.style.display="block"}else{document.all.id.style.display="none"}}}};</script>
JS;
				static::$_jsDisplayed = true;
        }

        echo '<div class="bhive-dump" style="font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">'.$callee['file'].' @ line: '.$callee['line'].'</h1>';
		echo '<pre style="overflow:auto;font-size:100%;">';

        $count = count($arguments);
        for ($i = 1; $i <= $count; $i++) {
            echo '<strong>Variable #' . $i . ':</strong>'. PHP_EOL;
            echo static::format('', $arguments[$i - 1]);
            echo PHP_EOL . PHP_EOL;
        }

        echo '</pre>';
        echo '</div>';
    }

    /**
	 * Quick and nice way to output a mixed variable to the browser
	 *
	 * @return	string
	 */
    public static function inspect()
    {
        $backtrace = debug_backtrace();

        // If begin called from within, show the file above
        if (strpos($backtrace[0]['file'], 'core/classes/debug.php') !== false) {
            $callee = $backtrace[1];
            $label = Inflection::humanize($backtrace[1]['function']);
        } else {
            $callee = $backtrace[0];
            $label = 'Debug';
        }

        $arguments = func_get_args();
        $totalArguments = count($arguments);

        $callee['file'] = App::cleanPath($callee['file']);

        if ( ! static::$_jsDisplayed) {
            echo <<<JS
<script type="text/javascript">function bhive_debug_toggle(a){if(document.getElementById){if(document.getElementById(a).style.display=="none"){document.getElementById(a).style.display="block"}else{document.getElementById(a).style.display="none"}}else{if(document.layers){if(document.id.display=="none"){document.id.display="block"}else{document.id.display="none"}}else{if(document.all.id.style.display=="none"){document.all.id.style.display="block"}else{document.all.id.style.display="none"}}}};</script>
JS;
            static::$_jsDisplayed = true;
        }

        echo '<div class="bhive-inspect" style="font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">'.$callee['file'].' @ line: '.$callee['line'].'</h1>';
		echo '<pre style="overflow:auto;font-size:100%;">';
		$i = 0;
		foreach ($arguments as $argument)
		{
			echo '<strong>'.$label.' #'.(++$i).' of '.$totalArguments.'</strong>:<br />';
				echo static::format('...', $argument);
			echo '<br />';
		}

		echo "</pre>";
		echo "</div>";

    }

    /**
	 * Formats the given $var's output in a nice looking, Foldable interface.
	 *
	 * @param	string	$name	the name of the var
	 * @param	mixed	$var	the variable
	 * @param	int		$level	the indentation level
	 * @param	string	$indentChar	the indentation character
	 * @return	string	the formatted string.
	 */
	public static function format($name, $var, $level = 0, $indentChar = '&nbsp;&nbsp;&nbsp;&nbsp;', $scope = '')
	{
        $return = str_repeat($indentChar, $level);
        if (is_array($var)) {
            $id = 'bhive_debug_' . mt_rand();
            $return .= "<i>{$scope}</i> <strong>" . htmlentities($name) . "</strong>";
            $return .= " (Array, " . count($var) . " element" . (count($var) != 1 ? "s" : "") . ")";

            if (count($var) > 0 && static::$maxNestingLevel > $level) {
                $return .= " <a href=\"javascript:bhive_debug_toggle('$id');\" title=\"Click to ".(static::$jsToggleOpen ? "close" : "open")."\">&crarr;</a>\n";
            } else {
                $return .= "\n";
            }

            if (static::$maxNestingLevel <= $level) {
                $return .= str_repeat($indentChar, $level + 1) . "...\n";
            } else {
                $subReturn = '';
                foreach ($var as $key => $val) {
                    $subReturn .= static::format($key, $val, $level + 1);
                }

                if (count($var) > 0) {
                    $return .= "<span id=\"$id\" style=\"display: ".(static::$jsToggleOpen ? "block" : "none").";\">$subReturn</span>";
                } else {
                    $return .= $subReturn;
                }
            }
        }
        elseif (is_string($var))
		{
			//$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (String): <span style=\"color:#E00000;\">\"".\Security::htmlentities($var)."\"</span> (".strlen($var)." characters)\n";
            $return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (String): <span style=\"color:#E00000;\">\"".$var."\"</span> (".strlen($var)." characters)\n";
        }
		elseif (is_float($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Float): {$var}\n";
		}
		elseif (is_long($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Integer): {$var}\n";
		}
		elseif (is_null($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> : null\n";
		}
		elseif (is_bool($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Boolean): ".($var ? 'true' : 'false')."\n";
		}
		elseif (is_double($var))
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong> (Double): {$var}\n";
		}
		elseif (is_object($var))
		{
			// dirty hack to get the object id
			ob_start();
			var_dump($var);
			$contents = ob_get_contents();
			ob_end_clean();

			// process it based on the xdebug presence and configuration
			if (extension_loaded('xdebug') and ini_get('xdebug.overload_var_dump') === '1')
			{
				if (ini_get('html_errors'))
				{
					preg_match('~(.*?)\)\[<i>(\d+)(.*)~', $contents, $matches);
				}
				else
				{
					preg_match('~class (.*?)#(\d+)(.*)~', $contents, $matches);
				}
			}
			else
			{
				preg_match('~object\((.*?)#(\d+)(.*)~', $contents, $matches);
			}

			$id = 'bhive_debug_'.mt_rand();
			$rvar = new \ReflectionObject($var);
			$vars = $rvar->getProperties();
			$return .= "<i>{$scope}</i> <strong>{$name}</strong> (Object #".$matches[2]."): ".get_class($var);
			if (count($vars) > 0 and static::$maxNestingLevel > $level)
			{
				$return .= " <a href=\"javascript:bhive_debug_toggle('$id');\" title=\"Click to ".(static::$jsToggleOpen ? "close" : "open")."\">&crarr;</a>\n";
			}
			$return .= "\n";

			$subReturn = '';
			foreach ($rvar->getProperties() as $prop)
			{
				$prop->isPublic() or $prop->setAccessible(true);
				if ($prop->isPrivate())
				{
					$scope = 'private';
				}
				elseif ($prop->isProtected())
				{
					$scope = 'protected';
				}
				else
				{
					$scope = 'public';
				}
				if (static::$maxNestingLevel <= $level)
				{
					$subReturn .= str_repeat($indentChar, $level + 1)."...\n";
				}
				else
				{
					$subReturn .= static::format($prop->name, $prop->getValue($var), $level + 1, $indentChar, $scope);
				}
			}

			if (count($vars) > 0)
			{
				$return .= "<span id=\"$id\" style=\"display: ".(static::$jsToggleOpen ? "block" : "none").";\">$subReturn</span>";
			}
			else
			{
				$return .= $subReturn;
			}
		}
		else
		{
			$return .= "<i>{$scope}</i> <strong>".htmlentities($name)."</strong>: {$var}\n";
		}
		return $return;
    }

    /**
	 * Returns the debug lines from the specified file
	 *
	 * @param	string   $filePath		the file path
	 * @param	int      $lineNum		the line number
	 * @param	bool     $highlihgt		whether to use syntax highlighting or not
	 * @param	int      $padding		the amount of line padding
	 * @return	array
	 */
    public static function fileLines($filepath, $lineNum, $highlight = true, $padding = 5)
    {
        // Deal with eval'd code
        if (strpos($filepath, 'eval()\'d code') !== false) {
            return '';
        }

        // We cache the entire file to reduce disk IO for multiple errors
        if ( ! isset(static::$files[$filepath])) {
            static::$files[$filepath] = file($filepath, FILE_IGNORE_NEW_LINES);
            array_unshift(static::$files[$filepath], '');
        }

        $start = $lineNum - $padding;
        if ($start < 0) {
            $start = 0;
        }

        $length = ($lineNum - $start) + $padding + 1;
        if (($start + $length) > count(static::$files[$filepath]) - 1) {
            $length = NULL;
        }

        $debugLines = array_slice(static::$files[$filepath], $start, $length, TRUE);

        if ($highlight) {
            $toReplace = array('<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', "\n");
            $replaceWith = array('', '', '<span style="color: #0000BB">', '');

            foreach ($debugLines as & $line) {
                $line = str_replace($toReplace, $replaceWith, highlight_string('<?php ' . $line, TRUE));
            }
        }

        return $debugLines;
    }

    /**
     * Output the call stack from here, or the supplied one.
     *
     * @param  array $trace (optional) A backtrace to output
     * @return string       Formatted backtrace
     */
    public static function backtrace($trace = null)
    {
        $trace or $trace = debug_backtrace();

        return static::dump($trace);
    }

    /**
	* Prints a list of all currently declared classes.
	*/
	public static function classes()
	{
		return static::dump(get_declared_classes());
	}

    /**
	* Prints a list of all currently declared interfaces (PHP5 only).
	*/
	public static function interfaces()
	{
		return static::dump(get_declared_interfaces());
	}

    /**
	* Prints a list of all currently included (or required) files.
	*/
	public static function includes()
	{
	return static::dump(get_included_files());
	}

    /**
	 * Prints a list of all currently declared functions.
	 */
	public static function functions()
	{
		return static::dump(get_defined_functions());
	}

    /**
	 * Prints a list of all currently declared constants.
	 */
	public static function constants()
	{
		return static::dump(get_defined_constants());
	}

    /**
	 * Prints a list of all currently loaded PHP extensions.
	 */
	public static function extensions()
	{
		return static::dump(get_loaded_extensions());
	}

    /**
	 * Prints a list of all HTTP request headers.
	 */
	public static function headers()
	{
		// get the current request headers and dump them
		return static::dump(Input::headers());
	}

    /**
	 * Prints a list of the configuration settings read from <i>php.ini</i>
	 */
	public static function phpini()
	{
		if ( ! is_readable(get_cfg_var('cfg_file_path')))
		{
			return false;
		}

		// render it
		return static::dump(parse_ini_file(get_cfg_var('cfg_file_path'), true));
	}

}
