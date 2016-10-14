<?php
abstract class SabaiFramework_Criteria_Array extends SabaiFramework_Criteria
{
    private $_field;
    private $_array;

    public function __construct($field, array $array)
    {
        $this->_field = $field;
        $this->_array = $array;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getArray()
    {
        return $this->_array;
    }
}
