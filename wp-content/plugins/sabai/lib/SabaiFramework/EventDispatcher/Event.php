<?php
class SabaiFramework_EventDispatcher_Event
{
    private $_type, $_vars;

    public function __construct($type, array $vars = array())
    {
        $this->_type = $type;
        $this->_vars = $vars;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getVars()
    {
        return $this->_vars;
    }
}