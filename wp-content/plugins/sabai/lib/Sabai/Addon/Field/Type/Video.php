<?php
class Sabai_Addon_Field_Type_Video extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Video', 'sabai'),
            'default_widget' => 'video',
            'default_renderer' => 'video',
            'default_settings' => array(),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'id' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'was' => 'id',
                    'length' => 20,
                ),
                'provider' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'length' => 20,
                    'notnull' => true,
                    'was' => 'provider',
                ),
            ),
            'indexes' => array(
                'id' => array(
                    'fields' => array('id' => array('sorting' => 'ascending')),
                    'was' => 'id',
                ),
            ),
        );
    }
    
    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $value) {
            if (!is_array($value) || !is_string($value['id']) || strlen($value['id']) === 0) continue;

            $ret[] = $value + array('provider' => 'youtube');
        }

        return $ret;
    }
}