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
* View class
*
* @package		BHive
* @subpackage	Core
*/
class View
{
    protected $_variables = [];
    protected $_bodyClasses = [];
    protected $_controller;
    protected $_action;

    /**
     * Creates a new view
     * @param type $controller
     * @param type $action
     */
    public function __construct($controller, $action)
    {
        $this->_controller = strtolower($controller);
        $this->_action = $action;
    }

    /**
     * Set variables
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->_variables[$name] = $value;
    }

    /**
     * Returns a variable
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->_variables[$name];
    }

    /**
     * Set the view's title, if none is given the app's name is used
     * @param  string $title The title
     */
    public function title($title = null)
    {
        if ( ! isset($title)) {
            $title = App::$name;
        }
        $this->_variables['PAGE_TITLE'] = $title;
    }

    /**
     * Adds a body class
     * @param string|array $class The class or classes to add
     */
    public function addBodyClass($class)
    {
        if (is_array($class)) {
            $this->_bodyClasses = array_merge($this->_bodyClasses, $class);
        } else {
            $this->_bodyClasses[] = $class;
        }
    }

    /**
     * Returns the body classes if any are set
     * @return string The body class(es)
     */
    public function getBodyClassString()
    {
        if ( ! empty($this->_bodyClasses)) {
            return implode(' ', $this->_bodyClasses);
        }
    }

    /**
     * Display the template if a controller specific footer or header is not found
     * the global header & footer in the view folder will be used
     * @param boolean $doNotRenderHeader enables not outputting headers for a particular action
     *                                   This can be used in AJAX calls.
     */
    public function render($renderHeader = true)
    {
        // If page title is not yet set; do so
        if ($renderHeader && ! array_key_exists('PAGE_TITLE', $this->_variables)) {
            $this->title();
        }

        extract($this->_variables);

        if ($renderHeader) {
            if (file_exists(APPPATH . 'views' . DS . $this->_controller . DS . 'header.php')) {
                    include APPPATH . 'views' . DS . $this->_controller . DS . 'header.php';
            } else {
                    include APPPATH . 'views' . DS . 'header.php';
            }
        } else {
            // Include script if available
            $script = Asset::js($this->_controller . DS . $this->_action);
            if ($script) {
                echo '<script type="text/javascript" src="' . $script . '"></script>';
            }
        }

        if (file_exists(APPPATH . 'views' . DS . $this->_controller . DS . $this->_action . '.php')) {
            include APPPATH . 'views' . DS . $this->_controller . DS . $this->_action . '.php';
        }

        if ($renderHeader) {
            if (file_exists(APPPATH . 'views' . DS . $this->_controller . DS . 'footer.php')) {
                    include APPPATH . 'views' . DS . $this->_controller . DS . 'footer.php';
            } else {
                    include APPPATH . 'views' . DS . 'footer.php';
            }
        }
    }
}
