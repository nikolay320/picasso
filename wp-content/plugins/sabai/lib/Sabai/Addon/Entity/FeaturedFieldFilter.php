<?php
class Sabai_Addon_Entity_FeaturedFieldFilter extends Sabai_Addon_Field_Filter_Boolean
{
    protected $_fieldType, $_nullOnly = true;
    
    public function __construct(Sabai_Addon $addon, $name, $fieldType)
    {
        parent::__construct($addon, $name);
        $this->_fieldType = $fieldType;
    }
    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Featured/Unfeatured', 'sabai'),
            'field_types' => array($this->_fieldType),
            'default_settings' => array(
                'type' => 'checkbox',
                'inline' => true,
                'labels' => array(
                    'on' => __('Featured', 'sabai'),
                    'off' => __('Unfeatured', 'sabai'),
                    'any' => _x('Any', 'option', 'sabai'),
                ),
                'checkbox_label' => __('Show featured only', 'sabai'),
            ),
            'creatable' => false,
        );
    }
}