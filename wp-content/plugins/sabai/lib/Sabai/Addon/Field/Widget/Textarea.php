<?php
class Sabai_Addon_Field_Widget_Textarea extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Textarea field', 'sabai'),
            'field_types' => array('text'),
            'default_settings' => array(
                'rows' => 5,
                'nl2br' => false,
            ),
            'repeatable' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'rows' => array(
                '#type' => 'number',
                '#title' => __('Rows', 'sabai'),
                '#size' => 5,
                '#integer' => true,
                '#default_value' => $settings['rows'],
            ),
            'nl2br' => array(
                '#type' => 'checkbox',
                '#title' => __('Preserve line breaks'),
                '#default_value' => $settings['nl2br'],
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        return array(
            '#type' => 'textarea',
            '#rows' => $settings['rows'],
            '#default_value' => isset($value) ? $value['value'] : null,
            '#min_length' => isset($field_settings['min_length']) ? $field_settings['min_length'] : null,
            '#max_length' => isset($field_settings['max_length']) ? $field_settings['max_length'] : null,
            '#char_validation' => isset($field_settings['char_validation']) ? $field_settings['char_validation'] : 'none',
            '#regex' => isset($field_settings['regex']) ? $field_settings['regex'] : null,
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $default_value = $field->getFieldDefaultValue();
        return sprintf('<textarea rows="%d" style="width:100%%;" disabled="disabled">%s</textarea>', $settings['rows'], isset($default_value) ? Sabai::h($default_value[0]) : '');
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            '#type' => 'textarea',
            '#rows' => $settings['rows'] > 5 ? 5 : $settings['rows'],
        );
    }
    
    public function fieldWidgetFormatText(Sabai_Addon_Field_IField $field, array $settings, $value, Sabai_Addon_Entity_IEntity $entity)
    {
        if (!strlen($value)) {
            return '';
        }
        $value = strip_tags($value);
        if (!empty($settings['nl2br'])) {
            $value = nl2br($value);
        }
        return '<p>' . $value . '</p>';
    }
}