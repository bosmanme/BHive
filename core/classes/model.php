<?php
/**
 * Main model class
 *
 * Classes that extend this model and have variables witch contain data
 * should be protected or private or will be empty when called
 */
class Model extends ORM
{

	protected $_tableName;
	protected $_model;

    // Default dates to keep tracking of database changes
    protected $createdAt;
    protected $updatedAt;
    protected $deletedAt;

    /**
     * Create a new model
     * @param int $id The unique id of the record to load (if any)
     */
	public function __construct($id = null)
	{

		$this->_model = get_class($this);

		Config::load('db.php');
		$settings = Config::get('database');

		self::configure('connectionString', 'mysql:host=' . $settings['host'] . ';port=' . $settings['port'] . ';dbname=' . $settings['name']);
        self::configure('username', $settings['user']);
        self::configure('password', $settings['pass']);

        $this->_tableName = $settings['pref'] . strtolower(Inflection::pluralize($this->_model));

        parent::_setupDb();

        if ($id) {
            $this->_loadData($id);
        }
	}

    /**
     * Load database data based on the unique ID
     * @param mixed $id
     */
    private function _loadData($id)
	{
        $this->_data = $this->ignoreCache()->findOne($id)->asArray();
    }

}
