<?php
// mysql_affeceted_rows() returns 0 if no data is modified
// even there was a match, not desirable for implementing
// the optimistic offline locking pattern in which we need
// to return false or 0 only when no matching record was found.
// We can change this behaviour of mysql by supplying the
// following constant to mysql_connect()
if (!defined('MYSQL_CLIENT_FOUND_ROWS')) {
    define('MYSQL_CLIENT_FOUND_ROWS', 2);
}

class SabaiFramework_DB_Connection_MySQL extends SabaiFramework_DB_Connection
{
    /**
     * @var string
     */
    protected $_resourceHost;
    /**
     * @var string
     */
    protected $_resourcePort;
    /**
     * @var string
     */
    protected $_resourceUser;
    /**
     * @var string
     */
    protected $_resourceUserPassword;
    /**
     * @var bool
     */
    protected $_resourceSecure;
    /**
     * @var string
     */
    protected $_clientEncoding;
    private $_clientFlags;

    /**
     * @var array
     */
    protected static $_charsets = array(
        'utf-8' => 'utf8',
        'big5' => 'big5',
        'cp-866' => 'cp866',
        'euc-jp' => 'ujis',
        'euc-kr' => 'euckr',
        'gb2312' => 'gb2312',
        'gbk' => 'gbk',
        'iso-8859-1' => 'latin1',
        'iso-8859-2' => 'latin2',
        'iso-8859-7' => 'greek',
        'iso-8859-8' => 'hebrew',
        'iso-8859-8-i' => 'hebrew',
        'iso-8859-9' => 'latin5',
        'iso-8859-13' => 'latin7',
        'iso-8859-15' => 'latin1',
        'koi8-r' => 'koi8r',
        'shift_jis' => 'sjis',
        'tis-620' => 'tis620',
    );

    /**
     * Constructor
     *
     * @return SabaiFramework_DB_Connection_MySQL
     */
    public function __construct(array $config)
    {
        parent::__construct('MySQL');
        $this->_resourceName = $config['dbname'];
        $this->_resourceHost = $config['host'];
        $this->_resourcePort = !empty($config['port']) ? $config['port'] : 3306;
        $this->_resourceUser = $config['user'];
        $this->_resourceUserPassword = $config['pass'];
        $this->_resourceSecure = !empty($config['secure']);
        $this->_charset = @$config['charset'];
    }

    /**
     * Connects to the mysql server and DB
     *
     * @return resource
     * @throws SabaiFramework_DB_ConnectionException
     */
    protected function _doConnect()
    {
        $this->_clientFlags = $this->_resourceSecure ? MYSQL_CLIENT_FOUND_ROWS | MYSQL_CLIENT_SSL : MYSQL_CLIENT_FOUND_ROWS;
        $host = $this->_resourceHost . ':' . $this->_resourcePort;
        $link = mysql_connect($host, $this->_resourceUser, $this->_resourceUserPassword, true, $this->_clientFlags);
        if ($link === false) {
            throw new SabaiFramework_DB_ConnectionException(sprintf('Unable to connect to database server @%s', $this->_resourceHost));
        }
        if (!mysql_select_db($this->_resourceName, $link)) {
            throw new SabaiFramework_DB_ConnectionException(sprintf('Unable to connect to database %s', $this->_resourceName));
        }

        // Set client encoding if requested
        if (!empty($this->_charset)
            && ($encoding = $this->_getClientEncoding($this->_charset))
        ) {
            if (function_exists('mysql_set_charset')) {
                $result = mysql_set_charset($encoding, $link);
            } else {
                $result = mysql_query('SET NAMES ' . $encoding, $link);
            }
            if ($result) $this->_clientEncoding = $encoding;
        }

        return $link;
    }

    public function getDSN()
    {
        return sprintf('mysql://%s:%s@%s:%s/%s?client_flags=%d',
            rawurlencode($this->_resourceUser),
            rawurlencode($this->_resourceUserPassword),
            rawurlencode($this->_resourceHost),
            rawurlencode($this->_resourcePort),
            rawurlencode($this->_resourceName),
            $this->_clientFlags
        );
    }

    private function _getClientEncoding($charset)
    {
        // Return the original if no mapping is required
        if (in_array($charset, self::$_charsets)) return $charset;

        return @self::$_charsets[strtolower($charset)];
    }
}