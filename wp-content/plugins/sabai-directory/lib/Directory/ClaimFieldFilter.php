<?php
class Sabai_Addon_Directory_ClaimFieldFilter extends Sabai_Addon_Field_Filter_Boolean
{
    protected $_filterColumn = 'claimed_by', $_nullOnly = true;
    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Claimed/Unclaimed', 'sabai-directory'),
            'field_types' => array('directory_claim'),
            'default_settings' => array(
                'type' => 'checkbox',
                'inline' => true,
                'labels' => array(
                    'on' => __('Claimed', 'sabai-directory'),
                    'off' => __('Unclaimed', 'sabai-directory'),
                ),
                'checkbox_label' => __('Show claimed only', 'sabai-directory'),
            ),
            'creatable' => false,
        );
    }
}