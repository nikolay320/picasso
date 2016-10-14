<?php
class Sabai_Platform_WordPress_DBConnection extends SabaiFramework_DB_Connection
{
    public function __construct()
    {
        parent::__construct(@$GLOBALS['wpdb']->use_mysqli ? 'MySQLi' : 'MySQL');
        $this->_resourceName = $GLOBALS['wpdb']->dbname;
        $this->_clientEncoding = $GLOBALS['wpdb']->charset;
    }

    protected function _doConnect()
    {
        return $GLOBALS['wpdb']->dbh;
    }

    public function getDSN()
    {
        return sprintf('%s://%s:%s@%s/%s?client_flags=%d',
            strtolower($this->_scheme),
            rawurlencode($GLOBALS['wpdb']->dbuser),
            rawurlencode($GLOBALS['wpdb']->dbpassword),
            rawurlencode($GLOBALS['wpdb']->dbhost),
            rawurlencode($GLOBALS['wpdb']->dbname),
            @$GLOBALS['wpdb']->use_mysqli
                ? (defined('MYSQLI_CLIENT_FLAGS') ? MYSQLI_CLIENT_FLAGS : 0)
                : (defined('MYSQL_CLIENT_FLAGS') ? MYSQL_CLIENT_FLAGS : 0)
        );
    }
}