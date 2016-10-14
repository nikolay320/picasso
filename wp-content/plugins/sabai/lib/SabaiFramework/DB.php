<?php
abstract class SabaiFramework_DB
{
    /**
     * @var SabaiFramework_DB_Connection
     */
    protected $_connection;
    /**
     * @var string
     */
    protected $_resourcePrefix;
    /**
     * @var string
     */
    private $_version;
    /**
     * @var int
     */
    private $_versionAsInt;
    /**
     * @var array SQL queries that have issued
     */
    protected $_queries = array();

    /**
     * Creates an instance of SabaiFramework_DB
     *
     * @param SabaiFramework_DB_Connection $connection
     * @param string $tablePrefix
     * @return SabaiFramework_DB
     */
    public static function factory(SabaiFramework_DB_Connection $connection, $tablePrefix)
    {
        $scheme = $connection->getScheme();
        $class = 'SabaiFramework_DB_' . $scheme;
        if (!class_exists($class, false)) {
            $file = 'SabaiFramework/DB/' . $scheme . '.php';
            require $file;
        }
        $db = new $class($connection);
        $db->setResourcePrefix($tablePrefix);

        return $db;
    }

    /**
     * Constructor
     *
     * @param SabaiFramework_DB_Connection $connection
     */
    protected function __construct(SabaiFramework_DB_Connection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Gets the database connection object
     *
     * @return SabaiFramework_DB_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Gets the name of prefix used in datasource
     *
     * @return string
     */
    public function getResourcePrefix()
    {
        return $this->_resourcePrefix;
    }

    /**
     * Sets the name of prefix used in datasource
     *
     * @param string $prefix
     */
    public function setResourcePrefix($prefix)
    {
        $this->_resourcePrefix = $prefix;
    }

    /**
     * Returns optional config varaibles for creating database tables, used by MDB2_Schema
     *
     * @return array
     */
    public function getMDB2CreateTableOptions()
    {
        return array();
    }

    /**
     * Begins a transaction
     *
     * @throws SabaiFramework_DB_QueryException
     */
    public function begin()
    {
        $this->exec('BEGIN');
    }

    /**
     * Commits a transaction
     *
     * @throws SabaiFramework_DB_QueryException
     */
    public function commit()
    {
        $this->exec('COMMIT');
    }

    /**
     * Performs a rollback of transaction
     *
     * @throws SabaiFramework_DB_QueryException
     */
    public function rollback()
    {
        $this->exec('ROLLBACK');
    }

    /**
     * Queries the database
     *
     * @param string $sql
     * @param int $limit
     * @param int $offset
     * @return SabaiFramework_DB_Rowset
     * @throws SabaiFramework_DB_QueryException
     */
    public function query($sql, $limit = 0, $offset = 0)
    {
        $this->_queries[] = $query = $this->getQuery($sql, $limit, $offset);
        if (!$rs = $this->_doQuery($query)) {
            throw new SabaiFramework_DB_QueryException(sprintf('%s SQL: %s', $this->lastError(), $query));
        }
        return $rs;
    }

    /**
     * Executes an SQL
     *
     * @param string $sql
     * @return int The number of rows affected.
     * @throws SabaiFramework_DB_QueryException
     */
    public function exec($sql)
    {
        $this->_queries[] = $sql;
        if (!$this->_doExec($sql)) {
            throw new SabaiFramework_DB_QueryException(sprintf('%s SQL: %s', $this->lastError(), $sql));
        }
        return $this->affectedRows();
    }

    /**
     * Gets queries that have been issued
     * @return array
     */
    public function getQueries()
    {
        return $this->_queries;
    }

    /**
     * Checks if the server version is at least the requested version
     *
     * @protected
     * @param string $base
     * @param string $operator
     * @param bool $explode
     * @return bool
     */
    public function checkVersion($base, $operator = '==', $explode = true)
    {
        if ($explode) {
            $base = explode('.', $base);
            $base = $base[0] * 10000 + intval(@$base[1]) * 100 + intval(@$base[2]);
        }
        $version = $this->getVersion(true);
        switch ($operator) {
            case '<':
            case 'lt':
                return $version < $base;

            case '<=':
            case 'le':
                return $version <= $base;

            case '>=':
            case 'ge':
                return $version >= $base;

            case '>':
            case 'gt':
                return $version > $base;

            case '!=':
            case '<>':
            case 'ne':
                return $version != $base;

            default:
                return $version == $base;
        }
    }

    /**
     * Gets the version of data source
     * @param bool $asInt
     * @param mixed Version string or integer
     */
    public function getVersion($asInt = false)
    {
        if (!isset($this->_version)) $this->_version = $this->_doGetVersion();
        if (!$asInt) return $this->_version;

        if (!isset($this->_versionAsInt)) {
            $versions = explode('.', $this->_version);
            $this->_versionAsInt = $versions[0] * 10000 + intval(@$versions[1]) * 100 + intval(@$versions[2]);
        }

        return $this->_versionAsInt;
    }

    abstract public function getQuery($sql, $limit = 0, $offset = 0);
    abstract protected function _doQuery($sql);
    abstract protected function _doExec($sql);
    abstract public function affectedRows();
    abstract public function lastInsertId($tableName, $keyName);
    abstract public function lastError();
    abstract public function escapeBool($value);
    abstract public function escapeString($value);
    abstract public function escapeBlob($value);
    abstract public function unescapeBlob($value);
    abstract protected function _doGetVersion();
}