<?php
class Sabai_Addon_Date_DatePickerFieldFilter extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Date', 'sabai'),
            'field_types' => array('date_timestamp'),
            'default_settings' => array(
                'num_months' => 1,
            ),
        );
    }
    
    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        return array(
            'num_months' => array(
                '#type' => 'number',
                '#title' => __('Number of months to show in date picker', 'sabai'),
                '#default_value' => $settings['num_months'],
                '#integer' => true,
                '#min_value' => 1,
                '#max_value' => 5,
                '#size' => 3,
            ),
        );
    }
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        $field_settings = $field->getFieldSettings();
        return array(
            '#class' => 'sabai-form-inline',
            '#element_validate' => array(array($this, 'validateDates')),
            '#collapsible' => false,
            'from' => array(
                '#type' => 'date_datepicker',
                '#min_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_min'] : null,
                '#max_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_max'] : null,
                '#disable_time' => true,
                '#field_suffix' => _x('to', 'range separator', 'sabai'),
                '#number_months' => $settings['num_months'],
            ),
            'to' => array(
                '#type' => 'date_datepicker',
                '#min_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_min'] : null,
                '#max_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_max'] : null,
                '#disable_time' => '23:59',
                '#number_months' => $settings['num_months'],
            ),
        );
    }
    
    public function validateDates($form, &$value, $element)
    {        
        if (!empty($value['from']) && !empty($value['to'])) {
            if ($value['from'] >= $value['to']) {
                $form->setError(__('The second date must be later than the first date.', 'sabai'), $element['#name']);
            }
        }
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        if (empty($value['from']) && empty($value['to'])) return false;
        
        if (!empty($value['from'])) {
            $value['from'] = is_numeric($value['from']) ? intval($value['from']) : strtotime($value['from']);
        }
        if (!empty($value['to'])) {
            $value['to'] = is_numeric($value['to']) ? intval($value['to']) : strtotime($value['to']);
        }

        return true;
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        if (!empty($value['from'])) {
            $query->addIsOrGreaterThanCriteria($field, 'value', $value['from']);
        }
        if (!empty($value['to'])) {
            $query->addIsOrSmallerThanCriteria($field, 'value', $value['to']);
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        return sprintf('<input type="text" disabled="disabled" size="8" /> %s <input type="text" disabled="disabled" size="8" />', _x('to', 'range separator', 'sabai'));
    }
}