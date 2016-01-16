<?php

/**
 * The core controller class
 */
abstract class Controller extends App
{

	protected $_controller;
	protected $_action;
	protected $_models;
	protected $_view;

	/**
     * Create a controller instance
     * @param string $controller
     * @param string $action
     * @param boolean $loadModel set to false if default model does not have to be loaded
     */
	public function __construct($controller, $action, $loadModel = true)
	{
		parent::__construct();

		$this->_controller = ucfirst($controller);
		$this->_action = $action;

		$this->_view = new View($controller, $action);

		// If a model with the same name exists, load it
		if ($loadModel) {
			$this->_loadModel($controller);
		}
	}

	/**
     * Load a model in the controller
     * @global Inflect $inflect
     * @param string $model
     */
	protected function _loadModel($model)
	{

		$model = ucfirst(Inflection::singularize($model));

		if (class_exists($model)) {
			$this->_models[$model] = new $model();
		}
	}

    /**
     * Return a model from the controller or the default one if not set
     * @param string $model
     * @return boolean|Model
     */
	protected function _getModel($model = false)
	{

        if ( ! $model) {
            $model = ucfirst(Inflection::singularize($this->_controller));
        }

        if ($model) {
            if (is_object($this->_models[$model])) {
                return $this->_models[$model];
            } else {
                return false;
            }
        }
	}

    /**
     * Returns the controller's view
     * @return View
     */
	protected function _getView()
	{
		return $this->_view;
	}

    /**
     * Method executed before any action
     */
    public function beforeAction() {}

    /**
     * Method executed after any action
     */
    public function afterAction(){}
}
