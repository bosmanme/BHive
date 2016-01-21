<?php
/**
 *
 * @package    BHive
 * @version    1.0
 * @author     Mathias Bosman
 * @license    MIT License
 * @copyright  2016 - Mathias Bosman
 */

/**
 * Home controller
 *
 * The Home controller, controlling the index page, doesn't really do anything..
 *
 * @package app
 * @extends Controller
 */
class Controller_Home extends Controller
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
        $test = "test";
        $this->_getView()->set('test', $test);
        $this->_getView()->render();
    }
}
