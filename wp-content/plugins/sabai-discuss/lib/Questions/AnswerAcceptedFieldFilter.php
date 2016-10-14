<?php
class Sabai_Addon_Questions_AnswerAcceptedFieldFilter extends Sabai_Addon_Field_Filter_Boolean
{
    protected $_filterColumn = 'score', $_trueValue = array(1, 2, 3);
    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Accepted/Unaccepted', 'sabai-discuss'),
            'field_types' => array('questions_answer_accepted'),
            'default_settings' => array(
                'type' => 'checkbox',
                'inline' => true,
                'labels' => array(
                    'on' => __('Accepted', 'sabai-discuss'),
                    'off' => __('Unaccepted', 'sabai-discuss'),
                ),
                'checkbox_label' => __('Show accepted answers only', 'sabai-discuss'),
            ),
            'creatable' => false,
        );
    }
}