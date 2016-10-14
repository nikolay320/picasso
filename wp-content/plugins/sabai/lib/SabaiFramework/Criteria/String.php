<?php
abstract class SabaiFramework_Criteria_String extends SabaiFramework_Criteria
{
    private $_field;
    private $_string;

    public function __construct($field, $string)
    {
        $this->_field = $field;
        $this->_string = strval($string);
    }

    public function getField()
    {
        return $this->_field;
    }

    public function getString()
    {
        return $this->_string;
    }
}
