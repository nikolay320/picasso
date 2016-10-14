<?php
class Sabai_Addon_Time_FieldWidget extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Time picker', 'sabai'),
            'field_types' => array('time_time'),
            'default_settings' => array(
                'current_time_selected' => false,
            ),
            'is_fieldset' => true,
            'repeatable' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'current_time_selected' => array(
                '#type' => 'checkbox',
                '#title' => __('Set current time selected by default', 'sabai'),
                '#default_value' => !empty($settings['current_time_selected']),
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        return array(
            '#type' => 'time_time',
            '#current_time_selected' => !empty($settings['current_time_selected']),
            '#default_value' => $value,
            '#disable_day' => empty($field_settings['enable_day']),
            '#disable_end' => empty($field_settings['enable_end']),
            '#start_of_week' => $this->_addon->getApplication()->getPlatform()->getStartOfWeek(),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        $ret = array();
        $time = '';
        if ($settings['current_time_selected']) {
             $time = date('h:i A', time());
        }
        if (!empty($field_settings['enable_day'])) {
            $days = $this->_addon->getApplication()->Time_Days();
            $ret[] = '<select disabled="disabled"><option>' . array_shift($days) . '</option></select>';
        }
        $ret[] = '<input type="text" disabled="disabled" size="6" placeholder="HH:MM" value="' . $time . '" />';
        if (!empty($field_settings['enable_end'])) {
            $ret[] = '<input type="text" disabled="disabled" size="6" placeholder="HH:MM" />';
        }
        return implode(PHP_EOL, $ret);
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array()){}
}