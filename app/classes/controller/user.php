<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    BHive
 * @version    1.0
 * @author     Mathias Bosman
 * @license    MIT License
 * @copyright  2016 - Mathias Bosman
 */

/**
 * User controller
 *
 * The User controller - currently an example
 *
 * @package app
 * @extends Controller
 */
use \Model\User;

class Controller_User extends Controller
{
    /**
     * Generates the index page (home page)
     */
    public function __construct($controller, $action)
    {
        // There's no Home model, so do not load it by default
    	parent::__construct($controller, $action, false);
    }

    /**
     * The index, main page, welcome page.. etc..
     */
    public function index()
    {
        $this->_getView()->set('var', '');
        $this->_getView()->render();
    }

    public function login() {
        $this->_getView()->render();
    }
}
