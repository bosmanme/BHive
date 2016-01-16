<?php
/**
 * Home controller
 *
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
        $this->_getView()->set('test', "test");
        $this->_getView()->render();
    }
}
?>
