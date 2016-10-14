<?php
class Sabai_Addon_Questions_OpenFieldFilter extends Sabai_Addon_Field_Filter_Boolean
{
    protected $_inverse = true;
    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Open/Closed', 'sabai-discuss'),
            'field_types' => array('questions_closed'),
            'default_settings' => array(
                'type' => 'checkbox',
                'inline' => true,
                'labels' => array(
                    'on' => __('Open', 'sabai-discuss'),
                    'off' => __('Closed', 'sabai-discuss'),
                ),
                'checkbox_label' => __('Show open questions only', 'sabai-discuss'),
            ),
            'creatable' => false,
        );
    }
}