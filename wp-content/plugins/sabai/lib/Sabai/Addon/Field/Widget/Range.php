<?php
class Sabai_Addon_Field_Widget_Range extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Range input field', 'sabai'),
            'field_types' => array('range'),
            'default_settings' => array(
                'size' => 5,
                'range_separator' => _x('to', 'range separator', 'sabai'),
                'step' => 1,
            ),
            'repeatable' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'size' => array(
                '#type' => 'number',
                '#title' => __('Field size', 'sabai'),
                '#integer' => true,
                '#min_value' => 0,
                '#default_value' => $settings['size'],
                '#size' => 5,
            ),
            'range_separator' => array(
                '#type' => 'textfield',
                '#title' => __('Range separator', 'sabai'),
                '#default_value' => $settings['range_separator'],
                '#size' => 10,
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
        
        return array(
            '#type' => 'range',
            '#default_value' => $value,
            '#integer' => empty($field_settings['decimals']),
            '#min_value' => $min,
            '#max_value' => $max,
            '#step' => !empty($settings['step']) ? $settings['step'] : $this->_getStep($field),
            '#field_prefix' => isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? $field_settings['prefix'] : null,
            '#field_suffix' => isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? $field_settings['suffix'] : null,
            '#range_separator' => $settings['range_separator'],
            '#size' => $settings['size'],
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        $prefix = isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? sprintf('<span class="sabai-form-field-prefix">%s</span>', $field_settings['prefix']) : '';
        $suffix = isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? sprintf('<span class="sabai-form-field-suffix">%s</span>', $field_settings['suffix']) : '';
        $default_value = $field->getFieldDefaultValue();
        return sprintf(
            '%1$s<input type="text" value="%2$s" size="%3$d" disabled="disabled" /> %4$s <input type="text" value="%5$s" size="%3$d" disabled="disabled" />%6$s',
            $prefix,
            isset($default_value[0]['min']) ? Sabai::h($default_value[0]['min']) : '',
            $settings['size'],
            $settings['range_separator'],
            isset($default_value[0]['max']) ? Sabai::h($default_value[0]['max']) : '',
            $suffix
        );
    }
}