<?php
/**
 * Base class on which controllers are based
 */
class App
{

    /**
     * Create new App class instance
     */
	function __construct()
	{
		$this->_setRepporting();
		$this->_removeMagicQuotes();
		$this->_unregisterGlobals();
	}

    /**
     * Sets the error reporting level based on the environment
     */
	private function _setRepporting()
	{
		if (DEVELOPMENT_ENVIRONMENT == true) {
			error_reporting(E_ALL);
		}
	}

	/**
	 * Strips slashes from a given variable
	 * @param  Mixed $value Either an array of other object
	 * @return Mixed
	 */
	private function _stripSlashesDeep($value)
	{
		$value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripcslashes($value);

		return $value;
	}

	/**
	 * If get_magic_quotes is enabled, remove slashes from global variables
	 */
	private function _removeMagicQuotes()
	{
		if (get_magic_quotes_gpc()) {
			$_GET 		= $this->_stripSlashesDeep($_GET);
			$_POST 		= $this->_stripSlashesDeep($_POST);
			$_COOKIE	= $this->_stripSlashesDeep($_COOKIE);
		}
	}

	/**
	 * Just in case... (register globals was removed in php 5.4)
	 */
	private function _unregisterGlobals()
	{
		if (ini_get('register_globals')) {
			$array = array(
				'_SESSION',
				'_POST',
				'_GET',
				'_COOKIE',
				'_REQUEST',
				'_SERVER',
				'_ENV',
				'_FILES');

			foreach ($array as $value) {
				foreach ($GLOBALS[$value] as $key => $var) {
					if ($var === $GLOBALS[$key]) {
						unset($GLOBALS[$key]);
					}
				}
			}
		}
	}

}
?>
