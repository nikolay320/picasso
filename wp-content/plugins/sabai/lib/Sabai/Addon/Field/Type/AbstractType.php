<?php
abstract class Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_IType
{
    protected $_addon, $_name, $_info;

    public function __construct(Sabai_Addon $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = $this->_fieldTypeGetInfo();
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }
    
    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {

    }
    
    public function fieldTypeGetSchema(array $settings)
    {

    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values){}

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity){}
    
    public function fieldTypeOnExport(Sabai_Addon_Field_IField $field, array &$values){}
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        return array_values($currentLoadedValue) !== array_values($valueToSave);
    }

    public function validateMinMaxSettings($form, &$value, $element, $decimalField = false)
    {
        $integer = $decimalField && empty($value[$decimalField]);
        if (isset($value['min'])) {
            if (!strlen($value['min'])) {
                unset($value['min']);
            } elseif ($integer) {
                $value['min'] = intval($value['min']);
            }
        }
        if (isset($value['max'])) {
            if (!strlen($value['max'])) {
                unset($value['max']);
            } elseif ($integer) {
                $value['max'] = intval($value['max']);
            }
        }
        if (isset($value['min']) && isset($value['max'])) {
            if ($value['min'] >= $value['max']) {
                $form->setError(__('The value must be greater than the first value.', 'sabai'), $element['#name'] . '[max]');
            }
        }
    }

    abstract protected function _fieldTypeGetInfo();
}