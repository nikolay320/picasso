<?php
class Sabai_Addon_Date_DatePickerFieldWidget extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Date/Time picker', 'sabai'),
            'field_types' => array('date_timestamp'),
            'default_settings' => array(
                'current_date_selected' => false,
                'num_months' => 1,
            ),
            'is_fieldset' => true,
            'repeatable' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'current_date_selected' => array(
                '#type' => 'checkbox',
                '#title' => __('Set current date selected by default', 'sabai'),
                '#default_value' => !empty($settings['current_date_selected']),
            ),
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

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        return array(
            '#type' => 'date_datepicker',
            '#current_date_selected' => !empty($settings['current_date_selected']),
            '#min_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_min'] : null,
            '#max_date' => !empty($field_settings['date_range']) ? $field_settings['date_range_max'] : null,
            '#disable_time' => empty($field_settings['enable_time']),
            '#default_value' => $value,
            '#number_months' => $settings['num_months'],
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $date = $time = '';
        if ($settings['current_date_selected']) {
             $date = date('Y/m/d', time());
             $time = date('H:i', time());
        }
        $field_settings = $field->getFieldSettings();
        if (empty($field_settings['enable_time'])) {
            return sprintf('<input type="text" disabled="disabled" size="10" value="%s" />', $date);
        }
        return sprintf('<input type="text" disabled="disabled" size="8" value="%s" /><input type="text" disabled="disabled" size="6" placeholder="HH:MM" value="%s" />', $date, $time);
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
}