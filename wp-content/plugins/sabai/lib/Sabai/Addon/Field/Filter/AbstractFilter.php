<?php
abstract class Sabai_Addon_Field_Filter_AbstractFilter implements Sabai_Addon_Field_IFilter
{
    protected $_addon, $_name, $_info = array();

    public function __construct(Sabai_Addon $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldFilterGetInfo($key = null)
    {
        if (!isset($this->_info[$this->_name])) {
            $this->_info[$this->_name] = $this->_fieldFilterGetInfo();
        }

        return isset($key) ? @$this->_info[$this->_name][$key] : $this->_info[$this->_name];
    }
    
    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array()){}

    abstract protected function _fieldFilterGetInfo();
}