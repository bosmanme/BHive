<?php
/**
 * Main model class
 * 
 * Classes that extend this model and have variables witch contain data
 * should be protected or private or will be empty when called
 */
class Model extends ORM{

	protected $_tableName;
	protected $_model;
	
    // Default dates to keep tracking of database changes
    protected $createdAt;
    protected $updatedAt;
    protected $deletedAt;
    
    /**
     * Create a new model
     * @global Inflect $inflect
     * @param int $id The unique id of the record to load (if any)
     */
	public function __construct($id = null) {
        
        global $inflect;
		
		$this->_model = get_class($this);
		
		self::configure('connectionString', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME);
        self::configure('username', DB_USER);
        self::configure('password', DB_PASS);
        
        $this->_tableName = DB_PREF . strtolower($inflect->pluralize($this->_model));
        
        parent::_setupDb();
        
        if ($id) {
            $this->_loadData($id);
        }
	}
    
    /**
     * Load database data based on the unique ID
     * @param mixed $id
     */
    private function _loadData($id) {
        $this->_data = $this->ignoreCache()->findOne($id)->asArray();
    }
    
}
?>