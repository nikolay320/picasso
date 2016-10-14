<?php
class Sabai_Addon_Comment_FieldType extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Comments', 'sabai'),
            'entity_types' => array('content'),
            'creatable' => false,
        );
    }
}