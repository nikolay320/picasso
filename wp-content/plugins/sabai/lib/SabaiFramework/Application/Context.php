<?php
class SabaiFramework_Application_Context
{
    const STATUS_ERROR = 1, STATUS_SUCCESS = 2, STATUS_VIEW = 3;

    protected $_status = self::STATUS_VIEW;
    private $_attributes = array(), $_route;
    private static $_request;

    public function setRequest(SabaiFramework_Request $request)
    {
        self::$_request = $request;

        return $this;
    }

    /**
     *
     * @return SabaiFramework_Request 
     */
    public function getRequest()
    {
        return self::$_request;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function setAttributes(array $attributes, $merge = true)
    {
        $this->_attributes = $merge ? array_merge($this->_attributes, $attributes) : $attributes;

        return $this;
    }

    public function setRoute(SabaiFramework_Application_Route $route)
    {
        $this->_route = $route;

        return $this;
    }

    /**
     *
     * @return SabaiFramework_Application_Route 
     */
    public function getRoute()
    {
        return $this->_route;
    }

    public function setSuccess()
    {
        $this->_status = self::STATUS_SUCCESS;

        return $this;
    }

    public function isSuccess()
    {
        return $this->_status === self::STATUS_SUCCESS;
    }

    public function setError()
    {
        $this->_status = self::STATUS_ERROR;

        return $this;
    }

    public function isError()
    {
        return $this->_status === self::STATUS_ERROR;
    }

    public function setView()
    {
        $this->_status = self::STATUS_VIEW;

        return $this;
    }

    public function isView()
    {
        return $this->_status === self::STATUS_VIEW;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * PHP magic __get() method.
     * Return value by reference to suppress error when modifying an array.
     * @link http://bugs.php.net/bug.php?id=39449
     *
     * @param string $name
     * @return mixed
     */
    public function &__get($name)
    {
        return $this->_attributes[$name];
    }

    /**
     * PHP magic method
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     * PHP magic method
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_attributes[$name]);
    }

    /**
     * PHP magic method
     *
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->_attributes[$name]);
    }
}