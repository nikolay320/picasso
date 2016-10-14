<?php
abstract class SabaiFramework_Request
{
    /**
     * @var array
     */
    protected $_params;

    /**
     * Constructor
     * @param array $params
     */
    protected function __construct(array $params)
    {
        $this->_params = $params;
    }

    /**
     * Returns all request parameters
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Gets a request variable as a certain PHP type variable
     *
     * @access protected
     * @param string $type
     * @param string $name
     * @param mixed $default
     * @param array $include
     * @param array $exclude
     * @return mixed
     */
    protected function _as($type, $name, $default, array $include = null, array $exclude = null)
    {
        $ret = $default;
        if ($this->has($name)) {
            $ret = $this->get($name);
            if (@settype($ret, $type)) {
                if (isset($include)) {
                    if (!in_array($ret, $include, true)) {
                        $ret = $default;
                    }
                } elseif (isset($exclude)) {
                    if (in_array($ret, $exclude, true)) {
                        $ret = $default;
                    }
                }
            } else {
                $ret = $default;
            }
        }

        return $ret;
    }

    /**
     * Gets a certain request variable as array
     *
     * @param string $name
     * @param array $default
     * @param array $include
     * @param array $exclude
     * @return array
     */
    public function asArray($name, $default = array(), array $include = null, array $exclude = null)
    {
        return $this->_as('array', $name, $default, $include, $exclude);
    }

    /**
     * Gets a certain request variable as string
     *
     * @param string $name
     * @param string $default
     * @param mixed $include
     * @param mixed $exclude
     * @return string
     */
    public function asStr($name, $default = '', array $include = null, array $exclude = null)
    {
        return $this->_as('string', $name, $default, $include, $exclude);
    }

    /**
     * Gets a certain request variable as integer
     *
     * @param string $name
     * @param int $default
     * @param mixed $include
     * @param mixed $exclude
     * @return int
     */
    public function asInt($name, $default = 0, array $include = null, array $exclude = null)
    {
        return $this->_as('integer', $name, $default, $include, $exclude);
    }

    /**
     * Gets a certain request variable as bool
     *
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public function asBool($name, $default = false)
    {
        return $this->_as('boolean', $name, $default, null, null);
    }

    /**
     * Gets a certain request variable as float
     *
     * @param string $name
     * @param float $default
     * @param mixed $include
     * @param mixed $exclude
     * @return float
     */
    public function asFloat($name, $default = 0.0, array $include = null, array $exclude = null)
    {
        return $this->_as('float', $name, $default, $include, $exclude);
    }

    /**
     * Checks if a request parameter is present
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->_params[$name]);
    }

    /**
     * Gets the value of a request parameter
     *
     * @return mixed
     * @param string $name
     */
    public function get($name)
    {
        return @$this->_params[$name];
    }

    /**
     * Sets the value of a request parameter
     *
     * @param string $name
     * @param mixed $value
     * @return SabaiFramework_Request
     */
    public function set($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    public function __get($name)
    {
        return $this->_params[$name];
    }

    public function __set($name, $value)
    {
        $this->_params[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->_params[$name]);
    }

    public function __unset($name)
    {
        unset($this->_params[$name]);
    }
}
