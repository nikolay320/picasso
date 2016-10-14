<?php
class Sabai_Addon_Field_Widget_Checkbox extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Single checkbox', 'sabai'),
            'field_types' => array('boolean'),
            'default_settings' => array(
                'checkbox_label' => null,
                'checked' => false,
            ),
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'checkbox_label' => array(
                '#type' => 'textfield',
                '#title' => __('Checkbox label', 'sabai'),
                '#description' => __('Enter the label displayed next to the checkbox.', 'sabai'),
                '#default_value' => $settings['checkbox_label'],
            ),
            'checked' => array(
                '#type' => 'checkbox',
                '#title' => __('Make this field checked by default', 'sabai'),
                '#default_value' => !empty($settings['checked']),
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        return array(
            '#type' => 'checkbox',
            '#on_value' => 1,
            '#off_value' => 0,
            '#on_label' => $settings['checkbox_label'],
            '#default_value' => isset($value) ? $value : !empty($settings['checked']),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return sprintf('<input type="checkbox" disabled="disabled"%s /> %s', $settings['checked'] ? ' checked="checked"' : '', Sabai::h($settings['checkbox_label']));
    }
}