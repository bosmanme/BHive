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
* ORM class
*
* Handles all database queryies and connections
*
* @package		BHive
* @subpackage	Core
*/
class ORM
{

    // Where condition array keys
    const WHERE_FRAGMENT    = 0;
    const WHERE_VALUES      = 1;
    /**
     * Array of configuration values
     * @var array
     */
    protected static $_config = [
        'connection' 		=> 'sqlite::memory:',
        'idColumn' 			=> 'id',
        'idColumnOverrides' => array(),
        'errorMode' 		=> PDO::ERRMODE_EXCEPTION,
        'username' 			=> null,
        'password' 			=> null,
        'driverOptions' 	=> null,
        'identifyQuoteChar' => null, // we will auto detect this if not set
        'logging' 			=> false,
        'caching' 			=> true,
    ];
    /**
     * Database connection
     * @var PDO
     */
    protected static $_db;
    /**
     * The last query run, only saved when logging is enabled
     * @see ORM::$_config
     * @var string
     */
    protected static $_lastQuery;
    /**
     * All queries run, only saved when logging is enabled
     * @see ORM::$_config
     * @var array
     */
    protected static $_queryLog = [];
    /**
     * Query cache, only used when caching is enabled
     * @see ORM::$_config
     * @var array
     */
    protected static $_queryCache = [];
    /**
     * The name of the table for this ORM instance
     * @var string
     */
    protected $_tableName;
    /**
     * Alieas for the table used in queries
     * @var string
     */
    protected $_tableAlias;
    /**
     * Values to be bound to a query
     * @var array
     */
    protected $_values = [];
    /**
     * Columns to select in the result
     * @var array
     */
    protected $_resultColumns = ['*'];
    /**
     * Have the default result columns been changed manually?
     * @var boolean
     */
    protected $_usingDefaultResultColumns = true;
    /**
     * Joined sources
     * @var array
     */
    protected $_joinSources = [];
    /**
     * Create a distinct query?
     * @var boolean
     */
    protected $_distinct = false;
    /**
     * Is this a raw query?
     * @var boolean
     */
    protected $_isRawQuery = false;
    /**
     * The raw query itself
     * @var string
     */
    protected $_rawQuery = '';
    /**
     * The parameters for the raw query
     * @var array
     */
    protected $_rawParameters = [];
    /**
     * Array of where clauses
     * @var array
     */
    protected $_whereConditions = [];
    /**
     * The limit of the query
     * @var integer
     */
    protected $_limit = null;
    /**
     * The offset
     * @var integer
     */
    protected $_offset = null;
    /**
     * Order by clauses
     * @var array
     */
    protected $_orderBy = [];
    /**
     * Group by clauses
     * @var array
     */
    protected $_groupBy = [];
    /**
     * Data in case thys class gets hydrated
     * @var array
     */
    protected $_data = [];
    /**
     * Fields that have been changed during the process
     * @var array
     */
    protected $_dirtyFields = [];
    /**
     * Fields that are inserted
     * @var array
     */
    protected $_exprFields = [];
    /**
     * Is it is a new object (has create() been used)?
     * @var boolean
     */
    protected $_isNew = false;
    /**
     * Column to use as primary key, overriding the config settings
     * @var string
     */
    protected $_instanceIdColumn = null;

    /**
     * If set the cache will be ignored for this query
     * @var boolean
     */
    protected $_ignoreCache = false;
    /**
     * Set a config value
     * @param mixed $key Configuration setting to set (defaults to 'connectionString')
     * @param mixed $value
     */
    public static function configure($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $confKey => $confValue) {
                self::configure($confKey, $confValue);
            }
        } else {
            if (is_null($value)) {
                $value = $key;
                $key = 'connectionString';
            }
            self::$_config[$key] = $value;
        }
    }
    /**
     * Alias for the factory method
     * @param string $tableName
     * @see ORM::factory
     * @return ORM
     */
    public static function forTable($tableName)
    {
        return self::factory($tableName);
    }
    /**
     * The factory method
     * @param string $tableName Name of the table
     * @return ORM
     */
    public static function factory($tableName)
    {
        self::_setupDb();
        $factored = new self($tableName);
        return $factored;
    }
    /**
     * Set up the database connection
     */
    protected static function _setupDb()
    {

        if ( ! is_object(self::$_db)) {
            // Create the PDO connection
            $connectionString = self::$_config['connectionString'];
            $username = self::$_config['username'];
            $password = self::$_config['password'];
            $driverOptions = self::$_config['driverOptions'];
            $db = new PDO($connectionString, $username, $password, $driverOptions);
            $db->setAttribute(PDO::ATTR_ERRMODE, self::$_config['errorMode']);
            self::setDb($db);
        }
    }
    /**
     * Set the PDO object
     * @param PDO $db
     */
    public static function setDb($db)
    {
        self::$_db = $db;
        self::_setupIdentifierQuoteChar();
    }
    /**
     * Detect the character used to quote identifiers
     * such as table names, column names..
     * If manually specified this method will do nothing
     */
    public static function _setupIdentifierQuoteChar()
    {
        if (is_null(self::$_config['identifyQuoteChar'])) {
            self::$_config['identifyQuoteChar'] = self::_detectIdentifierQuoteChar();
        }
    }
    /**
     * Returns the character used to quote identifiers by looking at the driver
     */
    protected static function _detectIdentifierQuoteChar()
    {
        switch (self::$_db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'psql':
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
            case 'sybase':
                return '"';
            case 'mysql':
            case 'sqlite':
            case 'sqlite2':
            default:
                return '`';
        }
    }
    /**
     * Returns the PDO object used by the ORM
     * @return PDO
     */
    public static function getDb()
    {
        self::_setupDb();
        return self::$_db;
    }
    /**
     * Log the query, if so configurated
     * @param string $query
     * @param array $paramaters
     * @return boolean
     */
    protected static function _logQuery($query, $paramaters)
    {
        // check if logging is enabled
        if ( ! self::$_config['logging']) {
            return false;
        }
        if (count($paramaters) > 0) {
            // Escape the parameters
            $paramaters = array_map([self::$_db, 'quote'], $paramaters);
            // Avoid the use of %format
            $query = str_replace('%', '%%', $query);
            // Switch placeholders
            if (strpos($query, "'") !== false || strpos($query, '"') !== false) {
                $query = ORMString::strReplaceOutsideQuotes('?', '%s', $query);
            } else {
                $query = str_replace('?', '%s', $query);
            }

            $boundQuery = vsprintf($query, $paramaters);
        } else {
            $boundQuery = $query;
        }

        self::$_lastQuery = $boundQuery;
        self::$_queryLog[] = $boundQuery;

        return true;
    }

    /**
     * Get the last query executed. Only works if the
     * 'logging' config option is set to true. Otherwise
     * this will return null.
     * @return string
     */
    public static function getLastQuery()
    {
        return self::$_lastQuery;
    }

    /**
     * Get an array containing all the queries run up to
     * now. Only works if the 'logging' config option is
     * set to true. Otherwise returned array will be empty.
     * @return array
     */
    public static function getQueryLog()
    {
        return self::$_queryLog;
    }

    /**
     * constructor shouldn't be called directly.
     * Use the ORM::forTable factory method instead.
     * @param string $tableName
     * @param array $data
     * @see ORM::forTable
     */
    protected function __construct($tableName, $data=[])
    {
        $this->_tableName = $tableName;
        $this->_data = $data;
    }

    /**
     * Create a new, empty instance of the class. Used
     * to add a new row to your database. May optionally
     * be passed an associative array of data to populate
     * the instance. If so, all fields will be flagged as
     * dirty so all will be saved to the database when
     * save() is called.
     * @param array $data
     * @return ORM
     */
    public function create($data=null)
    {
        $this->_isNew = true;
        if ( ! is_null($data)) {
            return $this->hydrate($data)->forceAllDirty();
        }
        return $this;
    }

    /**
     * Specify the ID column to use for this instance or array of instances only.
     * This overrides the idColumn and idColumnOverrides settings.
     *
     * This is mostly useful for libraries built on top of this class, and will
     * not normally be used in manually built queries. If you don't know why
     * you would want to use this, you should probably just ignore it.
     * @param string|integer $idColumn
     * @return ORM
    */
    public function useIdColumn($idColumn)
    {
        $this->_instanceIdColumn = $idColumn;
        return $this;
    }

    /**
     * Create an ORM instance from the given row (an associative
     * array of data fetched from the database)
     * @param array $row
     * @return ORM
     */
    protected function _createInstanceFromRow($row)
    {
        $instance = self::forTable($this->_tableName);
        $instance->useIdColumn($this->_getIdColumnName());
        $instance->hydrate($row);
        return $instance;
    }

    /**
     * Look for a specific row
     * @param integer|string $id
     * @return ORM
     */
    public function findOne($id = null)
    {
        if ( ! is_null($id)) {
            $this->whereIdIs($id);
        }
        $this->limit(1);
        $rows = $this->_run();
        if (empty($rows)) {
            return false;
        }
        return $this->_createInstanceFromRow($rows[0]);
    }

    /**
     * Look up more then one row and return them as ORM instances
     * @return array|ORM
     */
    public function findMany()
    {
        $rows = $this->_run();
        return array_map([$this, '_createInstanceFromRow'], $rows);
    }
    /**
     * Returns a list of objects based on the called class
     * @return mixed|array
     */
    public function getList()
    {

        $find = $this->findMany();
        if ( ! empty($find)) {
            $list = [];
            // check if a prefix was added to the table name
            if (substr($this->_tableName, 0, strlen(DB_PREF)) === DB_PREF) {
                $tblName = preg_replace('/' . DB_PREF . '/', '', $this->_tableName);
            } else {
                $tblName = $this->_tableName;
            }
            $model = Inflection::singularize($tblName);

            foreach ($find as $object) {
                $list[] = new $model($object->id);
            }
            return $list;
        }
        return null;
    }
    /**
     * Look up multiple results as array
     * @return array
     */
    public function findArray()
    {
        return $this->_run();
    }

    /**
     * Returns the number of rows
     * @param type $column
     * @returnt integer
     */
    public function count($column = '*')
    {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Returns the max value of the chosen column
     * @param string $column
     * @return integer
     */
    public function max($column)
    {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Will return the average value of the chosen column
     * @param string $column
     * @return integer
     */
    public function avg($column)
    {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Returns the sum of all values of the chosen column
     * @param string $column
     * @return integer
     */
    public function sum($column)
    {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Performs an aggregate function on the current database
     * @param string $sqlFunction the function to call
     * @param string $column
     * @return integer
     */
    protected function _callAggregateDbFunction($sqlFunction, $column)
    {
        $alias = strtolower($sqlFunction);
        $sqlFunction = strtoupper($sqlFunction);

        if ($column != '*') {
            $column = $this->_quoteIdentifier($column);
        }

        $this->selectExpr("$sqlFunction($column)", $alias);
        $result = $this->findOne();
        return ($result !== false && isset($result->$alias)) ? (int) $result->$alias : 0;
    }

    /**
     * Populates class fields
     * @param array $data
     * @return ORM
     */
    public function hydrate($data = [])
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Force all data to be dirty, making them update when save() is called
     * @return ORM
     */
    public function forceAllDirty()
    {
        $this->_dirtyFields = $this->_data;
        return $this;
    }

    /**
     * Perform a raw query. The query can contain placeholders in
     * either named or question mark style. If placeholders are
     * used, the parameters should be an array of values which will
     * be bound to the placeholders in the query. If this method
     * is called, all other query building methods will be ignored.
     * @param string $query
     * @param array $parameters
     * @return ORM
     */
    public function rawQuery($query, $parameters = [])
    {
        $this->_isRawQuery = true;
        $this->_rawQuery = $query;
        $this->_rawParameters = $parameters;
        return $this;
    }

    /**
     * Add an alias for the main table to be used in queries
     * @param string $alias
     * @return ORM
     */
    public function tableAlias($alias)
    {
        $this->_tableAlias = $alias;
        return $this;
    }

    /**
     * Internal method to add an unquoted expression to the set
     * of columns returned by the select query. The second optional
     * argument is the alias to return the expression as.
     * @param string $expr
     * @param string $alias
     * @return ORM
     */
    protected function _addResultColumn($expr, $alias = null)
    {
        if ( ! is_null($alias)) {
            $expr .= " AS " . $this->_quoteIdentifier($alias);
        }
        if ($this->_usingDefaultResultColumns) {
            $this->_resultColumns = [$expr];
            $this->_usingDefaultResultColumns = false;
        } else {
            $this->_resultColumns[] = $expr;
        }
        return $this;
    }

    /**
     * Add a column to the list of columns returned by the SELECT
     * query. This defaults to '*'. The second optional argument is
     * the alias to return the column as.
     * @param string $column
     * @param string $alias
     * @return ORM
     */
    public function select($column, $alias=null)
    {
        $column = $this->_quoteIdentifier($column);
        return $this->_addResultColumn($column, $alias);
    }

    /**
     * Add an unquoted expression to the list of columns returned
     * by the select query. The second optional argument is
     * the alias to return the column as.
     * @param string $expr
     * @param string $alias
     * @return ORM
     */
    public function selectExpr($expr, $alias = null)
    {
        return $this->_addResultColumn($expr, $alias);
    }

    /**
     * Add columns to the list of columns returned by the select
     * query. This defaults to '*'. Many columns can be supplied
     * as either an array or as a list of parameters to the method.
     *
     * Note that the alias must not be numeric - if you want a
     * numeric alias then prepend it with some alpha chars. eg. a1
     *
     * @example selectMany(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5');
     * @example selectMany('column', 'column2', 'column3');
     * @example selectMany(array('column', 'column2', 'column3'), 'column4', 'column5');
     *
     * @return ORM
     */
    public function selectMany()
    {
        $columns = func_get_args();
        if( ! empty($columns)) {
            $columns = $this->_normaliseSelectManyColumns($columns);
            foreach($columns as $alias => $column) {
                if(is_numeric($alias)) {
                    $alias = null;
                }
                $this->select($column, $alias);
            }
        }
        return $this;
    }



    /**
     * Add an unquoted expression to the list of columns returned
     * by the select query. Many columns can be supplied as either
     * an array or as a list of parameters to the method.
     *
     * Note that the alias must not be numeric - if you want a
     * numeric alias then prepend it with some alpha chars. eg. a1
     *
     * @example selectManyExpr(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5')
     * @example selectManyExpr('column', 'column2', 'column3')
     * @example selectManyExpr(array('column', 'column2', 'column3'), 'column4', 'column5')
     *
     * @return ORM
     */
    public function selectManyExpr()
    {
        $columns = func_get_args();
        if( ! empty($columns)) {
            $columns = $this->_normaliseSelectManyColumns($columns);
            foreach($columns as $alias => $column) {
                if(is_numeric($alias)) {
                    $alias = null;
                }
                $this->selectExpr($column, $alias);
            }
        }
        return $this;
    }

    /**
     * Take a column specification for the select many methods and convert it
     * into a normalised array of columns and aliases.
     *
     * It is designed to turn the following styles into a normalised array:
     *
     * array(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5'))
     *
     * @param array $columns
     * @return array
     */
    protected function _normaliseSelectManyColumns($columns)
    {
        $return = [];
        foreach($columns as $column) {
            if(is_array($column)) {
                foreach($column as $key => $value) {
                    if( ! is_numeric($key)) {
                        $return[$key] = $value;
                    } else {
                        $return[] = $value;
                    }
                }
            } else {
                $return[] = $column;
            }
        }
        return $return;
    }

    /**
     * Add a distinct keyword before the list of columns in the select query
     */
    public function distinct()
    {
        $this->_distinct = true;
        return $this;
    }

    /**
     * Internal method to add a JOIN source to the query.
     *
     * @param string $joinOperator The joinOperator should be one of INNER, LEFT OUTER, CROSS etc - this
     * will be prepended to JOIN.
     * @param string $table should be the name of the table to join to.
     * @param string|array $constraint The constraint may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     *
     * firstColumn, operator, secondColumn
     *
     * Example: array('user.id', '=', 'profile.user_id')
     *
     * will compile to
     *
     * ON `user`.`id` = `profile`.`user_id`
     * @param string $tableAlias
     * @return ORM
     */
    protected function _addJoinSource($joinOperator, $table, $constraint, $tableAlias = null)
    {
        $joinOperator = trim("{$joinOperator} JOIN");
        $table = $this->_quoteIdentifier($table);

        // Add table alias if present
        if (! is_null($tableAlias)) {
            $tableAlias = $this->_quoteIdentifier($tableAlias);
            $table .= " {$tableAlias}";
        }
        // Build the constraint
        if (is_array($constraint)) {
            list($firstColumn, $operator, $secondColumn) = $constraint;
            $firstColumn = $this->_quoteIdentifier($firstColumn);
            $secondColumn = $this->_quoteIdentifier($secondColumn);
            $constraint = "{$firstColumn} {$operator} {$secondColumn}";
        }
        $this->_joinSources[] = "{$joinOperator} {$table} ON {$constraint}";
        return $this;
    }

    /**
     * Add a simple JOIN source to the query
     * @param string $table should be the name of the table to join to.
     * @param string|array $constraint The constraint may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     *
     * firstColumn, operator, secondColumn
     *
     * Example: array('user.id', '=', 'profile.user_id')
     *
     * will compile to
     *
     * ON `user`.`id` = `profile`.`user_id`
     * @param string $tableAlias
     * @return ORM
     */
    public function join($table, $constraint, $tableAlias = null)
    {
        return $this->_addJoinSource("", $table, $constraint, $tableAlias);
    }

    /**
     * Add a simple INNER JOIN source to the query
     * @param string $table should be the name of the table to join to.
     * @param string|array $constraint The constraint may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     *
     * firstColumn, operator, secondColumn
     *
     * Example: array('user.id', '=', 'profile.user_id')
     *
     * will compile to
     *
     * ON `user`.`id` = `profile`.`user_id`
     * @param string $tableAlias
     * @return ORM
     */
    public function innerJoin($table, $constraint, $tableAlias = null)
    {
        return $this->_addJoinSource("INNER", $table, $constraint, $tableAlias);
    }

    /**
     * Add a simple LEFT OUTER JOIN source to the query
     * @param string $table should be the name of the table to join to.
     * @param string|array $constraint The constraint may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     *
     * firstColumn, operator, secondColumn
     *
     * Example: array('user.id', '=', 'profile.user_id')
     *
     * will compile to
     *
     * ON `user`.`id` = `profile`.`user_id`
     * @param string $tableAlias
     * @return ORM
     */
    public function leftOuterJoin($table, $constraint, $tableAlias=null)
    {
        return $this->_addJoinSource("LEFT OUTER", $table, $constraint, $tableAlias);
    }
    /**
     * Add a simple RIGHT OUTER JOIN source to the query
     * @param string $table should be the name of the table to join to.
     * @param string|array $constraint The constraint may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     *
     * firstColumn, operator, secondColumn
     *
     * Example: array('user.id', '=', 'profile.user_id')
     *
     * will compile to
     *
     * ON `user`.`id` = `profile`.`user_id`
     * @param string $tableAlias
     * @return ORM
     */
    public function rightOuterJoin($table, $constraint, $tableAlias=null)
    {
        return $this->_addJoinSource("RIGHT OUTER", $table, $constraint, $tableAlias);
    }
    /**
     * Add a simple FULL OUTER JOIN source to the query
     * @param string $table should be the name of the table to join to.
     * @param string|array $constraint The constraint may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     *
     * firstColumn, operator, secondColumn
     *
     * Example: array('user.id', '=', 'profile.user_id')
     *
     * will compile to
     *
     * ON `user`.`id` = `profile`.`user_id`
     * @param string $tableAlias
     * @return ORM
     */
    public function fullOuterJoin($table, $constraint, $tableAlias=null)
    {
        return $this->_addJoinSource("FULL OUTER", $table, $constraint, $tableAlias);
    }

    /**
     * Internal method to add a WHERE condition to the query
     * @param string $fragment
     * @param array $values
     * @return ORM
     */
    protected function _addWhere($fragment, $values = [])
    {
        if (! is_array($values)) {
            $values = [$values];
        }
        $this->_whereConditions[] = [
            self::WHERE_FRAGMENT => $fragment,
            self::WHERE_VALUES => $values,
        ];
        return $this;
    }

    /**
     * Helper method to compile a simple COLUMN SEPARATOR VALUE
     * style WHERE condition into a string and value ready to
     * be passed to the _addWhere method. Avoids duplication
     * of the call to _quoteIdentifier
     * @param string $columnName
     * @param string $seperator
     * @param string $value
     * @return ORM
     */
    protected function _addSimpleWhere($columnName, $separator, $value)
    {
        // Add the table name in case of ambiguous columns
        if (count($this->_joinSources) > 0 && strpos($columnName, '.') === false) {
            $columnName = "{$this->_tableName}.{$columnName}";
        }
        $columnName = $this->_quoteIdentifier($columnName);
        return $this->_addWhere("{$columnName} {$separator} ?", $value);
    }
    /**
     * Return a string containing the given number of question marks,
     * separated by commas. Eg "?, ?, ?"
     * @param array $fields
     * @return string
     */
    protected function _createPlaceholders($fields)
    {
        if(!empty($fields)) {
            $db_fields = [];
            foreach($fields as $key => $value) {
                // Process expression fields directly into the query
                if(array_key_exists($key, $this->_exprFields)) {
                    $db_fields[] = $value;
                } else {
                    $db_fields[] = '?';
                }
            }
            return implode(', ', $db_fields);
        }
    }

    /**
     * Add a WHERE column = value clause to your query. Each time
     * this is called in the chain, an additional WHERE will be
     * added, and these will be ANDed together when the final query
     * is built.
     * @param string $columnName
     * @param string @value
     * @return ORM
     */
    public function where($columnName, $value)
    {
        return $this->whereEqual($columnName, $value);
    }
    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereEqual($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, '=', $value);
    }
    /**
     * Add a WHERE column != value clause to your query.
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereNotEqual($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, '!=', $value);
    }
    /**
     * Special method to query the table by its primary key
     * @param integer $id
     * @return ORM
     */
    public function whereIdIs($id)
    {
        return $this->where($this->_getIdColumnName(), $id);
    }
    /**
     * Add a WHERE ... LIKE clause to your query.
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereLike($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, 'LIKE', $value);
    }
    /**
     * Add where WHERE ... NOT LIKE clause to your query.
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereNotLike($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, 'NOT LIKE', $value);
    }
    /**
     * Add a WHERE ... > clause to your query
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereGt($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, '>', $value);
    }
    /**
     * Add a WHERE ... < clause to your query
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereLt($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, '<', $value);
    }
    /**
     * Add a WHERE ... >= clause to your query
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereGte($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, '>=', $value);
    }
    /**
     * Add a WHERE ... <= clause to your query
     * @param string $columnName
     * @param string $value
     * @return ORM
     */
    public function whereLte($columnName, $value)
    {
        return $this->_addSimpleWhere($columnName, '<=', $value);
    }
    /**
     * Add a WHERE ... IN clause to your query
     * @param string $columnName
     * @param array $values
     * @return ORM
     */
    public function whereIn($columnName, $values)
    {
        $columnName = $this->_quoteIdentifier($columnName);
        $placeholders = $this->_createPlaceholders($values);
        return $this->_addWhere("{$columnName} IN ({$placeholders})", $values);
    }
    /**
     * Add a WHERE ... NOT IN clause to your query
     * @param string $columnName
     * @param array $values
     * @return ORM
     */
    public function whereNotIn($columnName, $values)
    {
        $columnName = $this->_quoteIdentifier($columnName);
        $placeholders = $this->_createPlaceholders($values);
        return $this->_addWhere("{$columnName} NOT IN ({$placeholders})", $values);
    }
    /**
     * Add a WHERE column IS NULL clause to your query
     * @param string $columnName
     * @param array $values
     * @return ORM
     */
    public function whereNull($columnName)
    {
        $columnName = $this->_quoteIdentifier($columnName);
        return $this->_addWhere("{$columnName} IS NULL");
    }
    /**
     * Add a WHERE column IS NOT NULL clause to your query
     * @param string $columnName
     * @return ORM
     */
    public function whereNotNull($columnName)
    {
        $columnName = $this->_quoteIdentifier($columnName);
        return $this->_addWhere("{$columnName} IS NOT NULL");
    }
    /**
     * Add a raw WHERE clause to the query. The clause should
     * contain question mark placeholders, which will be bound
     * to the parameters supplied in the second argument.
     * @param string $clause
     * @param array $parameters
     * @return ORM
     */
    public function whereRaw($clause, $parameters = [])
    {
        return $this->_addWhere($clause, $parameters);
    }
    /**
     * Add a LIMIT to the query
     * @param integer $limit
     * @return ORM
     */
    public function limit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }
    /**
     * Add an OFFSET to the query
     * @param integer $offset
     * @return ORM
     */
    public function offset($offset)
    {
        $this->_offset = $offset;
        return $this;
    }
    /**
     * Add an ORDER BY clause to the query
     * @param string $columnName
     * @param string $ordering
     * @return ORM
     */
    protected function _addOrderBy($columnName, $ordering)
    {
        $columnName = $this->_quoteIdentifier($columnName);
        $this->_orderBy[] = "{$columnName} {$ordering}";
        return $this;
    }
    /**
     * Add an ORDER BY column DESC clause
     * @param string $columnName
     * @return ORM
     */
    public function orderByDesc($columnName)
    {
        return $this->_addOrderBy($columnName, 'DESC');
    }
    /**
     * Add an ORDER BY column ASC clause
     * @param string $columnName
     * @return ORM
     */
    public function orderByAsc($columnName)
    {
        return $this->_addOrderBy($columnName, 'ASC');
    }
    /**
     * Add an unquoted expression as an ORDER BY clause
     * @param string $clause
     * @return ORM
     */
    public function orderByExpr($clause)
    {
        $this->_order_by[] = $clause;
        return $this;
    }
    /**
     * Add a column to the list of columns to GROUP BY
     * @param string $columnName
     * @return ORM
     */
    public function groupBy($columnName)
    {
        $columnName = $this->_quoteIdentifier($columnName);
        $this->_groupBy[] = $columnName;
        return $this;
    }
    /**
     * Add an unquoted expression to the list of columns to GROUP BY
     * @param string $expr
     * @return ORM
     */
    public function groupByExpr($expr)
    {
        $this->_groupBy[] = $expr;
        return $this;
    }
    /**
     * Build a SELECT statement based on the clauses that have
     * been passed to this instance by chaining method calls.
     * @return ORM
     */
    protected function _buildSelect()
    {
        // If the query is raw, just set the $this->_values to be
        // the raw query parameters and return the raw query
        if ($this->_isRawQuery) {
            $this->_values = $this->_rawParameters;
            return $this->_rawQuery;
        }
        // Build and return the full SELECT statement by concatenating
        // the results of calling each separate builder method.
        return $this->_joinIfNotEmpty(" ", [
            $this->_buildSelectStart(),
            $this->_buildJoin(),
            $this->_buildWhere(),
            $this->_buildGroupBy(),
            $this->_buildOrderBy(),
            $this->_buildLimit(),
            $this->_buildOffset(),
        ]);
    }
    /**
     * Build the start of the SELECT statement
     * @return string
     */
    protected function _buildSelectStart()
    {
        $resultColumns = join(', ', $this->_resultColumns);
        if ($this->_distinct) {
            $resultColumns = 'DISTINCT ' . $resultColumns;
        }
        $fragment = "SELECT {$resultColumns} FROM " . $this->_quoteIdentifier($this->_tableName);
        if (! is_null($this->_tableAlias)) {
            $fragment .= " " . $this->_quoteIdentifier($this->_tableAlias);
        }
        return $fragment;
    }
    /**
     * Build the JOIN sources
     * @return string
     */
    protected function _buildJoin()
    {
        if (count($this->_joinSources) === 0) {
            return '';
        }
        return join(" ", $this->_joinSources);
    }
    /**
     * Build the WHERE clause(s)
     * @return string
     */
    protected function _buildWhere()
    {
        // If there are no WHERE clauses, return empty string
        if (count($this->_whereConditions) === 0) {
            return '';
        }
        $where_conditions = [];
        foreach ($this->_whereConditions as $condition) {
            $where_conditions[] = $condition[self::WHERE_FRAGMENT];
            $this->_values = array_merge($this->_values, $condition[self::WHERE_VALUES]);
        }
        return "WHERE " . join(" AND ", $where_conditions);
    }
    /**
     * Build GROUP BY
     * @return string
     */
    protected function _buildGroupBy()
    {
        if (count($this->_groupBy) === 0) {
            return '';
        }
        return "GROUP BY " . join(", ", $this->_groupBy);
    }
    /**
     * Build ORDER BY
     * @return string
     */
    protected function _buildOrderBy()
    {
        if (count($this->_orderBy) === 0) {
            return '';
        }
        return "ORDER BY " . join(", ", $this->_orderBy);
    }
    /**
     * Build LIMIT
     * @return string
     */
    protected function _buildLimit()
    {
        if (! is_null($this->_limit)) {
            return "LIMIT " . $this->_limit;
        }
        return '';
    }
    /**
     * Build OFFSET
     * @return string
     */
    protected function _buildOffset()
    {
        if (! is_null($this->_offset)) {
            return "OFFSET " . $this->_offset;
        }
        return '';
    }
    /**
     * Wrapper around PHP's join function which
     * only adds the pieces if they are not empty.
     * @param string $glue
     * @param array $pieces
     * @return array
     */
    protected function _joinIfNotEmpty($glue, $pieces)
    {
        $filtered_pieces = [];
        foreach ($pieces as $piece) {
            if (is_string($piece)) {
                $piece = trim($piece);
            }
            if (! empty($piece)) {
                $filtered_pieces[] = $piece;
            }
        }
        return join($glue, $filtered_pieces);
    }
    /**
     * Quote a string that is used as an identifier
     * (table names, column names etc). This method can
     * also deal with dot-separated identifiers eg table.column
     * @param string $identifier
     * @return string
     */
    protected function _quoteIdentifier($identifier)
    {
        $parts = explode('.', $identifier);
        $parts = array_map([$this, '_quoteIdentifierPart'], $parts);
        return join('.', $parts);
    }
    /**
     * This method performs the actual quoting of a single
     * part of an identifier, using the identifier quote
     * character specified in the config (or autodetected).
     * @param string $part
     * @return string
     */
    protected function _quoteIdentifierPart($part)
    {
        if ($part === '*') {
            return $part;
        }
        $quote_character = self::$_config['identifyQuoteChar'];
        return $quote_character . $part . $quote_character;
    }
    /**
     * Create a cache key for the given query and parameters.
     * @param string $query
     * @param array $parameter
     * @return string
     */
    protected static function _createCacheKey($query, $parameters)
    {
        $parameter_string = join(',', $parameters);
        $key = $query . ':' . $parameter_string;
        return sha1($key);
    }
    /**
     * Check the query cache for the given cache key. If a value
     * is cached for the key, return the value. Otherwise, return false.
     */
    protected static function _checkQueryCache($cache_key)
    {
        if (isset(self::$_queryCache[$cache_key])) {
            return self::$_queryCache[$cache_key];
        }
        return false;
    }
    /**
     * Clear the query cache
     */
    public static function clearCache()
    {
        self::$_queryCache = [];
    }
    /**
     * Add the given value to the query cache.
     */
    protected static function _cacheQueryResult($cache_key, $value)
    {
        self::$_queryCache[$cache_key] = $value;
    }

    /**
     * Forces to ignore the cache
     * @return \ORM
     */
    public function ignoreCache()
    {
        $this->_ignoreCache = true;
        return $this;
    }
    /**
     * Execute the SELECT query that has been built up by chaining methods
     * on this class. Return an array of rows as associative arrays.
     * @return array
     */
    protected function _run()
    {
        $query = $this->_buildSelect();
        $caching_enabled = self::$_config['caching'];
        $cache_key = self::_createCacheKey($query, $this->_values);

        if ($caching_enabled && !$this->_ignoreCache) {
            $cached_result = self::_checkQueryCache($cache_key);
            if ($cached_result !== false) {
                return $cached_result;
            }
        }
        self::_logQuery($query, $this->_values);
        $statement = self::$_db->prepare($query);
        $statement->execute($this->_values);
        $rows = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        if ($caching_enabled) {
            self::_cacheQueryResult($cache_key, $rows);
        }
        return $rows;
    }
    /**
     * Return the raw data wrapped by this ORM
     * instance as an associative array. Column
     * names may optionally be supplied as arguments,
     * if so, only those keys will be returned.
     * @return array
     */
    public function asArray()
    {
        if (func_num_args() === 0) {
            return $this->_data;
        }
        $args = func_get_args();
        return array_intersect_key($this->_data, array_flip($args));
    }
    /**
     * Return the value of a property of this object (database row)
     * or null if not present.
     * @return string|integer
     */
    public function get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }
    /**
     * Return the name of the column in the database table which contains
     * the primary key ID of the row.
     * @return string
     */
    protected function _getIdColumnName()
    {
        if (! is_null($this->_instanceIdColumn)) {
            return $this->_instanceIdColumn;
        }
        if (isset(self::$_config['idColumnOverrides'][$this->_tableName])) {
            return self::$_config['idColumnOverrides'][$this->_tableName];
        } else {
            return self::$_config['idColumn'];
        }
    }
    /**
     * Get the primary key ID of this object.
     * @return integer|string
     */
    public function id()
    {
        return $this->get($this->_getIdColumnName());
    }
    /**
     * Set a property to a particular value on this object.
     * To set multiple properties at once, pass an associative array
     * as the first parameter and leave out the second parameter.
     * Flags the properties as 'dirty' so they will be saved to the
     * database when save() is called.
     * @param string|integer|array $key
     * @param string|integer $value
     */
    public function set($key, $value = null)
    {
        $this->_setOrmProperty($key, $value);
    }
    public function set_expr($key, $value = null)
    {
        $this->_setOrmProperty($key, $value, true);
    }
    /**
     * Set a property on the ORM object.
     * @param string|array $key
     * @param string|null $value
     * @param boolean $expr Whether this value should be treated as raw or not
     */
    protected function _setOrmProperty($key, $value = null, $expr = false)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }
        foreach ($key as $field => $value) {
            $this->_data[$field] = $value;
            $this->_dirtyFields[$field] = $value;
            if (false === $expr and isset($this->_exprFields[$field])) {
                unset($this->_exprFields[$field]);
            } else if (true === $expr) {
                $this->_exprFields[$field] = true;
            }
        }
    }
    /**
     * Check whether the given field has been changed since this
     * object was saved.
     * @param string $key
     * @return boolean
     */
    public function isDirty($key)
    {
        return isset($this->_dirtyFields[$key]);
    }
    /**
     * Save any fields which have been modified on this object
     * to the database.
     * @return boolean|ORM
     */
    public function save()
    {
        $query = [];
        // remove any expression fields as they are already baked into the query
        $values = array_values(array_diff_key($this->_dirtyFields, $this->_exprFields));
        if (! $this->_isNew) { // UPDATE
            // If there are no dirty values, do nothing
            if (count($values) == 0) {
                return true;
            }
            $query = $this->_buildUpdate();
            $values[] = $this->id();
        } else { // INSERT
            $query = $this->_buildInsert();
        }
        self::_logQuery($query, $values);
        $statement = self::$_db->prepare($query);
        $success = $statement->execute($values);
        // If we've just inserted a new record, set the ID of this object
        if ($this->_isNew) {
            $this->_isNew = false;
            if (is_null($this->id())) {
                $this->_data[$this->_getIdColumnName()] = self::$_db->lastInsertId();
            }
        }
        $this->_dirtyFields = [];
        return $success;
    }

    /**
     * Build an UPDATE query
     * @return string
     */
    protected function _buildUpdate()
    {
        $query = [];
        $query[] = "UPDATE {$this->_quoteIdentifier($this->_tableName)} SET";
        $field_list = [];
        foreach ($this->_dirtyFields as $key => $value) {
            if (! array_key_exists($key, $this->_exprFields)) {
                $value = '?';
            }
            $field_list[] = "{$this->_quoteIdentifier($key)} = $value";
        }
        $query[] = join(", ", $field_list);
        $query[] = "WHERE";
        $query[] = $this->_quoteIdentifier($this->_getIdColumnName());
        $query[] = "= ?";
        return join(" ", $query);
    }
    /**
     * Build an INSERT query
     * @return string
     */
    protected function _buildInsert()
    {
        $query[] = "INSERT INTO";
        $query[] = $this->_quoteIdentifier($this->_tableName);
        $field_list = array_map(array($this, '_quoteIdentifier'), array_keys($this->_dirtyFields));
        $query[] = "(" . join(", ", $field_list) . ")";
        $query[] = "VALUES";
        $placeholders = $this->_createPlaceholders($this->_dirtyFields);
        $query[] = "({$placeholders})";
        return join(" ", $query);
    }
    /**
     * Delete this record from the database
     * @return boolean
     */
    public function delete()
    {
        $query = join(" ", [
            "DELETE FROM",
            $this->_quoteIdentifier($this->_tableName),
            "WHERE",
            $this->_quoteIdentifier($this->_getIdColumnName()),
            "= ?",
        ]);
        $params = [$this->id()];
        self::_logQuery($query, $params);

        $statement = self::$_db->prepare($query);
        return $statement->execute($params);
    }
    /**
     * Delete many records from the database
     * @return boolean
     */
    public function deleteMany()
    {
        // Build and return the full DELETE statement by concatenating
        // the results of calling each separate builder method.
        $query = $this->_joinIfNotEmpty(" ", [
            "DELETE FROM",
            $this->_quoteIdentifier($this->_tableName),
            $this->_buildWhere(),
        ]);
        $statement = self::$_db->prepare($query);
        return $statement->execute($this->_values);
    }
    public function __get($key)
    {
        return $this->get($key);
    }
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
    public function __unset($key)
    {
        unset($this->_data[$key]);
        unset($this->_dirtyFields[$key]);
    }
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }


}
/**
 * A class to handle str_replace operations that involve quoted strings
 * @example ORMString::strReplaceOutsideQuotes('?', '%s', 'columnA = "Hello?" AND columnB = ?');
 * @example ORMString::value('columnA = "Hello?" AND columnB = ?')->replaceOutsideQuotes('?', '%s');
 */
class ORMString
{
    protected $subject;
    protected $search;
    protected $replace;
    /**
     * Get an easy to use instance of the class
     * @param string $subject
     * @return \self
     */
    public static function value($subject)
    {
        return new self($subject);
    }
    /**
     * Shortcut method: Replace all occurrences of the search string with the replacement
     * string where they appear outside quotes.
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function strReplaceOutsideQuotes($search, $replace, $subject)
    {
        return self::value($subject)->replaceOutsideQuotes($search, $replace);
    }
    /**
     * Set the base string object
     * @param string $subject
     */
    public function __construct($subject)
    {
        $this->subject = (string) $subject;
    }
    /**
     * Replace all occurrences of the search string with the replacement
     * string where they appear outside quotes
     * @param string $search
     * @param string $replace
     * @return string
     */
    public function replaceOutsideQuotes($search, $replace)
    {
        $this->search = $search;
        $this->replace = $replace;
        return $this->_strReplaceOutsideQuotes();
    }
    /**
     * Validate an input string and perform a replace on all ocurrences
     * of $this->search with $this->replace
     * @return string
     */
    protected function _strReplaceOutsideQuotes()
    {
        $re_valid = '/
                # Validate string having embedded quoted substrings.
                ^                           # Anchor to start of string.
                (?:                         # Zero or more string chunks.
                  "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"  # Either a double quoted chunk,
                | \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'  # or a single quoted chunk,
                | [^\'"\\\\]+               # or an unquoted chunk (no escapes).
                )*                          # Zero or more string chunks.
                \z                          # Anchor to end of string.
                /sx';
        if (! preg_match($re_valid, $this->subject))
        {
            throw new ORMStringException("Subject string is not valid in the replace_outside_quotes context.");
        }
        $re_parse = '/
                # Match one chunk of a valid string having embedded quoted substrings.
                  (                         # Either $1: Quoted chunk.
                    "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"  # Either a double quoted chunk,
                  | \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'  # or a single quoted chunk.
                  )                         # End $1: Quoted chunk.
                | ([^\'"\\\\]+)             # or $2: an unquoted chunk (no escapes).
                /sx';
        return preg_replace_callback($re_parse, [$this, '_str_replace_outside_quotes_cb'], $this->subject);
    }
    /**
     * Process each matching chunk from preg_replace_callback replacing
     * each occurrence of $this->search with $this->replace
     * @param array $matches
     * @return string
     */
    protected function _strReplaceOutsideQuotesCb($matches)
    {
        // Return quoted string chunks (in group $1) unaltered.
        if ($matches[1])
            return $matches[1];
        // Process only unquoted chunks (in group $2).
        return preg_replace('/' . preg_quote($this->search, '/') . '/', $this->replace, $matches[2]);
    }
}
/**
 * A placeholder for exceptions eminating from the IdiormString class
 */
class ORMStringException extends Exception
{

}
