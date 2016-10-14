<?php
class Sabai_Addon_Field_Type_Boolean extends Sabai_Addon_Field_Type_Value
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('ON/OFF', 'sabai'),
            'default_widget' => 'checkbox',
            'default_renderer' => 'boolean',
            'default_settings' => array(),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                    'unsigned' => true,
                    'notnull' => true,
                    'was' => 'value',
                    'default' => false,
                ),
            ),
            'indexes' => array(
                'value' => array(
                    'fields' => array('value' => array('sorting' => 'ascending')),
                    'was' => 'value',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            $ret[]['value'] = is_array($value) ? (bool)$value['value'] : (bool)$value;
        }

        return $ret;
    }
    
    public function fieldTypeOnExport(Sabai_Addon_Field_IField $field, array &$values)
    {
        $values = array_map('intval', $values);
    }
}