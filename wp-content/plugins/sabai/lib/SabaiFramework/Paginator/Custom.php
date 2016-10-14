<?php
class SabaiFramework_Paginator_Custom extends SabaiFramework_Paginator
{
    protected $_getElementCountFunc;
    protected $_getElementsFunc;
    protected $_extraParams;
    protected $_extraParamsPrepend;
    protected $_emptyElements;

    public function __construct($getElementCountFunc, $getElementsFunc, $perpage, array $extraParams = array(), array $extraParamsPrepend = array(), $emptyElements = null, $key = 0)
    {
        parent::__construct($perpage, $key);
        $this->_getElementCountFunc = $getElementCountFunc;
        $this->_getElementsFunc = $getElementsFunc;
        $this->_extraParams = $extraParams;
        $this->_extraParamsPrepend = $extraParamsPrepend;
        $this->_emptyElements = $emptyElements;
    }

    protected function _getElementCount()
    {
        return call_user_func_array(
            $this->_getElementCountFunc,
            array_merge($this->_extraParamsPrepend, $this->_extraParams)
        );
    }

    protected function _getElements($limit, $offset)
    {
        return call_user_func_array(
            $this->_getElementsFunc,
            array_merge($this->_extraParamsPrepend, array($limit, $offset), $this->_extraParams)
        );
    }

    protected function _getEmptyElements()
    {
        return isset($this->_emptyElements) ? $this->_emptyElements : parent::_getEmptyElements();
    }
}