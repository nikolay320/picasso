<?php
class Sabai_Addon_Field_Widget_Slider extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Slider input field', 'sabai'),
            'field_types' => array('number'),
            'default_settings' => array(
                'size' => 'large',
                'step' => 1,
            ),
            'repeatable' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'size' => array(
                '#type' => 'select',
                '#title' => __('Field size', 'sabai'),
                '#options' => array(
                    'small' => __('Small', 'sabai'),
                    'medium' => __('Medium', 'sabai'),
                    'large' => __('Large (responsive)', 'sabai'),
                ),
                '#default_value' => $settings['size'],
            ),
            'step' => array(
                '#type' => 'number',
                '#title' => __('Range slider step', 'sabai'),
                '#default_value' => $settings['step'],
                '#size' => 5,
                '#numeric' => true,
                '#element_validate' => array(array($this, 'validateStep')),
                '#min_value' => 0,
            ),
        );
    }
    
    public function validateStep($form, &$value, $element)
    {
        if (empty($value)) return;
        
        $settings = $form->values['settings'];   
        $min_value = !empty($settings['min']) && is_numeric($settings['min']) ? $settings['min'] : 0;
        $max_value = !empty($settings['max']) && is_numeric($settings['max']) ? $settings['max'] : 100;
        
        $range = $max_value - $min_value;
        if ($range / $value <= 0
            || fmod($range, $value)
        ) {
            $form->setError(sprintf(__('The full specified value range of the slider (%s - %s) should be evenly divisible by the step', 'sabai'), $min_value, $max_value), $element);
        }
    }
    
    protected function _getStep(Sabai_Addon_Field_IField $field)
    {
        $settings = $field->getFieldSettings();
        return empty($settings['decimals']) ? 1 : ($settings['decimals'] == 1 ? 0.1 : 0.01);
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        $min = isset($field_settings['min']) ? $field_settings['min'] : null;
        $max = isset($field_settings['max']) ? $field_settings['max'] : null;
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);

        return array(
            '#type' => 'slider',
            '#default_value' => $value,
            '#integer' => empty($field_settings['decimals']),
            '#min_value' => $min,
            '#max_value' => $max,
            '#step' => !empty($settings['step']) ? $settings['step'] : $this->_getStep($field),
            '#field_prefix' => isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? $field_settings['prefix'] : null,
            '#field_suffix' => isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? $field_settings['suffix'] : null,
            '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        $prefix = isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? sprintf('<span class="sabai-form-field-prefix">%s</span>', $field_settings['prefix']) : '';
        $suffix = isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? sprintf('<span class="sabai-form-field-suffix">%s</span>', $field_settings['suffix']) : '';
        $default_value = $field->getFieldDefaultValue();
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        return sprintf(
            '%1$s<input type="text" value="%2$s"%3$s disabled="disabled" />%4$s',
            $prefix,
            isset($default_value[0]) ? Sabai::h($default_value[0]) : '',
            isset($settings['size']) && isset($sizes[$settings['size']]) ? sprintf(' size="%d"', $sizes[$settings['size']]) : '',
            $suffix
        );
    }
}