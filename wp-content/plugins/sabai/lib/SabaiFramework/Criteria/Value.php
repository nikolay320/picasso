<?php
abstract class SabaiFramework_Criteria_Value extends SabaiFramework_Criteria
{
    private $_field, $_value;

    public function __construct($field, $value)
    {
        $this->_field = $field;
        $this->_value = $value;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getValue()
    {
        return $this->_value;
    }
}
