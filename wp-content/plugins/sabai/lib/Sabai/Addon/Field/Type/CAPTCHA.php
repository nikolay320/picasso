<?php
class Sabai_Addon_Field_Type_CAPTCHA extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('CAPTCHA', 'sabai'),
            'cacheable' => false,
            'exportable' => false,
            'viewable' => false,
        );
    }
}