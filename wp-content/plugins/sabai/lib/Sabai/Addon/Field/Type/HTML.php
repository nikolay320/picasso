<?php
class Sabai_Addon_Field_Type_HTML extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('HTML', 'sabai'),
            'default_widget' => 'html',
            'viewable' => false,
            'exportable' => false,
        );
    }
}