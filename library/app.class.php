<?php
/**
 * Base class on which controllers are based
 */
class App {

    /**
     * Create new App class instance
     */
	function __construct() {
		$this->_setRepporting();
		$this->_removeMagicQuotes();
		$this->_unregisterGlobals();
	}
	
    /**
     * Sets the error reporting level based on the environment
     */
	private function _setRepporting() {
		if (DEVELOPMENT_ENVIRONMENT == true) {
			error_reporting(E_ALL);	
		}
	}
	
	private function _stripSlashesDeep($value) {
		$value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripcslashes($value);
		
		return $value;
	}
	
	private function _removeMagicQuotes() {
		if (get_magic_quotes_gpc()) {
			$_GET 		= $this->_stripSlashesDeep($_GET);
			$_POST 		= $this->_stripSlashesDeep($_POST);
			$_COOKIE	= $this->_stripSlashesDeep($_COOKIE);
		}
	}
	
	private function _unregisterGlobals() {
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