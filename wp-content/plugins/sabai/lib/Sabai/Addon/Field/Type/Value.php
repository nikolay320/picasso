<?php
abstract class Sabai_Addon_Field_Type_Value extends Sabai_Addon_Field_Type_AbstractType
{
    protected $_valueColumn = 'value';

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (is_array($value)) {
                if (empty($value) || !isset($value[$this->_valueColumn])) {
                    continue;
                }
                $value = (string)$value[$this->_valueColumn];
            }
            $value = (string)$value;
            if (strlen($value) === 0) continue;

            $ret[][$this->_valueColumn] = $value;
        }

        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $value[$this->_valueColumn];
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        $new = array();
        foreach ($valueToSave as $value) {
            $new[] = $value[$this->_valueColumn];
        }
        return $currentLoadedValue !== $new;
    }
}