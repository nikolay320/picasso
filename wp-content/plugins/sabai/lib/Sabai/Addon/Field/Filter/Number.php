<?php
class Sabai_Addon_Field_Filter_Number extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Text input field', 'sabai'),
            'field_types' => array('number', 'range'),
            'default_settings' => array(
                'size' => 5,
            ),
        );
    }

    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        if (isset($settings['size'])) {
            $size = $settings['size'];
        } else {
            $field_settings = $field->getFieldSettings();
            $size = isset($field_settings['max']) ? strlen((int)$field_settings['max']) + 1 : 10;
        }
        return array(
            'size' => array(
                '#type' => 'number',
                '#title' => __('Field size', 'sabai'),
                '#integer' => true,
                '#min_value' => 1,
                '#size' => 5,
                '#default_value' => $size,
            ),
        );
    }
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        $field_settings = $field->getFieldSettings();
        if ($field_settings['decimals'] > 0) {
            $numeric = true;
            $integer = false;
            $min_value = isset($field_settings['min']) && is_numeric($field_settings['min']) ? $field_settings['min'] : null;
            $max_value = isset($field_settings['max']) && is_numeric($field_settings['max']) ? $field_settings['max'] : null;
            $step = $field_settings['decimals'] == 1 ? 0.1 : 0.01;
        } else {
            $numeric = false;
            $integer = true;
            $min_value = isset($field_settings['min']) ? intval($field_settings['min']) : null;
            $max_value = isset($field_settings['max']) ? intval($field_settings['max']) : null;
            $step = null;
        }
        return array(
            '#type' => 'number',
            '#min_value' => $min_value,
            '#max_value' => $max_value,
            '#integer' => $integer,
            '#numeric' => $numeric,
            '#field_prefix' => isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? $field_settings['prefix'] : null,
            '#field_suffix' => isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? $field_settings['suffix'] : null,
            '#size' => empty($settings['size']) ? null : $settings['size'],
            '#step' => $step,
        );
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        return strlen((string)@$value) > 0;
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        switch ($field->getFieldType()) {
            case 'number':
                $query->addIsCriteria($field, 'value', $value);
                break;
            case 'range':
                $query->addIsOrSmallerThanCriteria($field, 'min', $value)
                    ->addIsOrGreaterThanCriteria($field, 'max', $value);
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        return sprintf(
            '%s<input type="text" disabled="disabled"%s />%s',
            isset($field_settings['prefix']) && strlen($field_settings['prefix']) ? $field_settings['prefix'] . ' ' : '',
            empty($settings['size']) ? '' : ' size="' . intval($settings['size']) . '"',
            isset($field_settings['suffix']) && strlen($field_settings['suffix']) ? ' ' . $field_settings['suffix'] : ''
        );
    }
}