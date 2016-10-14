<?php
class Sabai_Addon_Questions_ResolvedFieldFilter extends Sabai_Addon_Field_Filter_Boolean
{    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Resolved/Unresolved', 'sabai-discuss'),
            'field_types' => array('questions_resolved'),
            'default_settings' => array(
                'type' => 'checkbox',
                'inline' => true,
                'labels' => array(
                    'on' => __('Resolved', 'sabai-discuss'),
                    'off' => __('Unresolved', 'sabai-discuss'),
                ),
                'checkbox_label' => __('Show resolved only', 'sabai-discuss'),
            ),
            'creatable' => false,
        );
    }
}