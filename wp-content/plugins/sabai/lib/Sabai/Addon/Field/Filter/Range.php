<?php
class Sabai_Addon_Field_Filter_Range extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Range input field', 'sabai'),
            'field_types' => array('number', 'range'),
            'default_settings' => array(
                'size' => null,
                'range_separator' => _x('to', 'range separator', 'sabai'),
            ),
        );
    }

    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        $field_settings = $field->getFieldSettings();
        if (!isset($settings['size'])) {
            $settings['size'] = isset($field_settings['max']) ? strlen((int)$field_settings['max']) + 2 : 5;
        }
        return array(
            'size' => array(
                '#type' => 'number',
                '#title' => __('Field size', 'sabai'),
                '#integer' => true,
                '#min_value' => 1,
                '#size' => 5,
                '#default_value' => $settings['size'],
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
                '#default_value' => isset($settings['step']) ? $settings['step'] : $this->_getStep($field),
                '#size' => 5,
                '#numeric' => true,
                '#element_validate' => array(array(array($this, 'validateStep'), array($field_settings))),
                '#min_value' => 0,
            ),
        );
    }
    
    public function validateStep($form, &$value, $element, $settings)
    {
        if (empty($value)) return;
        
        $min_value = isset($settings['min']) ? $settings['min'] : 0;
        $max_value = isset($settings['max']) ? $settings['max'] : 100;
        
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
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        $field_settings = $field->getFieldSettings();        
        return array(
            '#type' => 'range',
            '#min_value' => isset($field_settings['min']) ? $field_settings['min'] : null,
            '#max_value' => isset($field_settings['max']) ? $field_settings['max'] : null,
            '#numeric' => true,
            '#field_prefix' => isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? $field_settings['prefix'] : null,
            '#field_suffix' => isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? $field_settings['suffix'] : null,
            '#size' => empty($settings['size']) ? null : $settings['size'],
            '#step' => !empty($settings['step']) ? $settings['step'] : $this->_getStep($field),
            '#range_separator' => $settings['range_separator'],
        );
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        return strlen((string)@$value['min']) || strlen((string)@$value['max']);
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        switch ($field->getFieldType()) {
            case 'number':
                if (strlen($value['min'])) {
                    $query->addIsOrGreaterThanCriteria($field, 'value', $value['min']);
                }
                if (strlen($value['max'])) {
                    $query->addIsOrSmallerThanCriteria($field, 'value', $value['max']);
                }
                break;
            case 'range':
                if (!strlen($value['min'])) {
                    $field_settings = $field->getFieldSettings();
                    $value['min'] = isset($field_settings['min']) ? $field_settings['min'] : 0;
                }
                if (!strlen($value['max'])) {
                    $field_settings = $field->getFieldSettings();
                    $value['max'] = isset($field_settings['max']) ? $field_settings['max'] : 100;
                }
                $query->addIsOrGreaterThanCriteria($field, 'min', $value['min'])
                    ->addIsOrSmallerThanCriteria($field, 'max', $value['max']);
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        $prefix = isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? sprintf('<span class="sabai-form-field-prefix">%s</span>', $field_settings['prefix']) : '';
        $suffix = isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? sprintf('<span class="sabai-form-field-suffix">%s</span>', $field_settings['suffix']) : '';
        return sprintf(
            '%1$s<input type="text" disabled="disabled"%2$s /> %3$s <input type="text" disabled="disabled"%2$s />%4$s',
            $prefix,
            empty($settings['size']) ? '' : ' size="' . intval($settings['size']) . '"',
            $settings['range_separator'],
            $suffix
        );
    }
}