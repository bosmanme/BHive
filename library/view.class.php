<?php
/**
 * View class
 *
 * Renders views
 *
 */
class View
{
    protected $_variables = [];
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
     * Display the template if a controller specific footer or header is not found
     * the global header & footer in the view folder will be used
     * @param boolean $doNotRenderHeader enables not outputting headers for a particular action
     *                                   This can be used in AJAX calls.
     */
    public function render($doNotRenderHeader = false)
    {
        $html = new HTML();

        if (file_exists(ROOT . DS . 'public' . DS . 'js' . DS . $this->_controller . DS . $this->_action . '.js')) {
            $script = $this->_controller . DS . $this->_action;
            $this->set('jsScript', $script);
        }


        extract($this->_variables);

        if ($doNotRenderHeader == false) {
            if (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'header.php')) {
                    include ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'header.php';
            } else {
                    include ROOT . DS . 'app' . DS . 'views' . DS . 'header.php';
            }
        } else {
            // Include scripts manually
            if ($script) {
                if (DEVELOPMENT_ENVIRONMENT) {
                    echo '<script type="text/javascript" src="public' . DS . 'js' . DS . $script . '.js?' . time() . '"></script>';
                } else {
                    echo '<script type="text/javascript" src="public' . DS . 'js' . DS . $script . '.js"></script>';
                }
            }
        }

        if (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php')) {

            include ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php';
        }

        if ($doNotRenderHeader == false) {
            if (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'footer.php')) {
                    include ROOT . DS . 'app' . DS . 'views' . DS . $this->_controller . DS . 'footer.php';
            } else {
                    include ROOT . DS . 'app' . DS . 'views' . DS . 'footer.php';
            }
        }
    }
}
?>
