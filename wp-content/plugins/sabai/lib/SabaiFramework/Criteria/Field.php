<?php
abstract class SabaiFramework_Criteria_Field extends SabaiFramework_Criteria
{
    private $_field;
    private $_field2;

    public function __construct($field1, $field2)
    {
        $this->_field = $field;
        $this->_field2 = $field2;
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getField2()
    {
        return $this->_field2;
    }
}
