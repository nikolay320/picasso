<?php
class SabaiFramework_Request_Http extends SabaiFramework_Request
{
    protected $_cookie;

    /**
     * Constructor
     *
     * @return SabaiFramework_Request_Web
     * @param bool $filterGlobals
     * @param bool $forceStripSlashes
     */
    public function __construct($filterGlobals = true, $forceStripSlashes = false, array $params = null, array $cookie = null)
    {
        if (!isset($params)) {
            switch (strtolower(self::method())) {
                case 'get':
                    $params = $_GET;
                    break;
                case 'put':
                    $put = array();
                    parse_str(file_get_contents('php://input'), $put);
                    $params = array_merge($_GET, $put);
                    break;
                default:
                    $params = array_merge($_GET, $_POST);
            }
        }

        if (!isset($cookie)) {
            $cookie = $_COOKIE;
        }

        if ($filterGlobals) {
            if ($forceStripSlashes || get_magic_quotes_gpc()) {
                $params = self::_stripSlashes($params);
                if (!empty($cookie)) {
                    $cookie = self::_stripSlashes($cookie);
                }
            }
            // Filter malicious user inputs
            $list = array('GLOBALS', '_GET', '_POST', '_REQUEST', '_COOKIE', '_ENV', '_FILES', '_SERVER', '_SESSION');
            self::_filterUserData($params, $list);
            if (!empty($cookie)) {
                self::_filterUserData($cookie, $list);
            }
        }

        parent::__construct($params);
        $this->_cookie = $cookie;
    }

    /**
     * @param mixed $var
     */
    protected static function _stripSlashes($var)
    {
        if (is_array($var)) {
            return array_map(array(__CLASS__, __FUNCTION__), $var);
        }

        return stripslashes($var);
    }

    /**
     * @param mixed $var
     * @param array $globalKeys
     */
    protected static function _filterUserData(&$var, $globalKeys = array())
    {
        if (is_array($var)) {
            $var_keys = array_keys($var);
            if (array_intersect($globalKeys, $var_keys)) {
                $var = array();
            } else {
                foreach ($var_keys as $key) {
                    self::_filterUserData($var[$key], $globalKeys);
                }
            }
        } else {
            $var = str_replace("\x00", '', $var);
        }
    }

    public function getCookie($name)
    {
        return isset($this->_cookie[$name]) ? $this->_cookie[$name] : null;
    }

    public static function header($name)
    {
        $php_name = 'HTTP_' . strtoupper($name);

        return isset($_SERVER[$php_name]) ? $_SERVER[$php_name] : null;
    }
    
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function isPostMethod()
    {
        return strcasecmp(self::method(), 'POST') === 0;
    }
    
    public static function url()
    {
        return sprintf(
            '%s://%s%s',
            !empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] ? 'https' : 'http',
            !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            self::uri()
        );
    }

    public static function uri()
    {
        if (empty($_SERVER['PHP_SELF']) || empty($_SERVER['REQUEST_URI'])) {
            // IIS
            $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

            if (!empty($_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
            }

            return $_SERVER['REQUEST_URI'];
        }

        return isset($_SERVER['ORIG_REQUEST_URI']) ? $_SERVER['ORIG_REQUEST_URI'] : $_SERVER['REQUEST_URI'];
    }

    public static function isXhr()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0);
    }

    public static function files($name)
    {
        if (empty($_FILES)) return array();

        if (isset($_FILES[$name])) return $_FILES[$name];

        if (false === $pos = strpos($name, '[')) return array();

        $base = substr($name, 0, $pos);
        $key = str_replace(array(']', '['), array('', '"]["'), substr($name, $pos + 1, -1));
        $code = array(sprintf('if (!isset($_FILES["%s"]["name"]["%s"])) return array();', $base, $key));
        $code[] = '$file = array();';
        foreach (array('name', 'type', 'size', 'tmp_name', 'error') as $property) {
            $code[] = sprintf('$file["%1$s"] = $_FILES["%2$s"]["%1$s"]["%3$s"];', $property, $base, $key);
        }
        $code[] = 'return $file;';

        return eval(implode(PHP_EOL, $code));
    }
}