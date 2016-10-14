<?php
class Sabai_Addon_Field_Widget_Checkboxes extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Checkboxes', 'sabai'),
            'field_types' => array('choice'),
            'accept_multiple' => true,
            'default_settings' => array(
                'inline' => false,
            ),
            'is_fieldset' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'inline' => array(
                '#type' => 'checkbox',
                '#title' => __('Display inline', 'sabai'),
                '#description' => __('Check this to align all options on the same line.', 'sabai'),
                '#default_value' => $settings['inline'],
            ),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        if (isset($value)) {
            $default_value = array();
            foreach ($value as $_value) {
                $default_value[] = $_value;
            }
            if (empty($default_value)) {
                $default_value = null; 
            }
        } else {
            $default_value = empty($field_settings['options']['default']) ? null : $field_settings['options']['default']; 
        }
        return array(
            '#type' => 'checkboxes',
            '#options' => $field_settings['options']['options'],
            '#class' => $settings['inline'] ? 'sabai-form-inline' : null,
            '#max_selection' => $field->getFieldMaxNumItems(),
            '#default_value' => $default_value,
        );
    }
        
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        $ret = array();
        foreach ($field_settings['options']['options'] as $value => $label) {
            $ret[] = sprintf('<input type="checkbox" disabled="disabled"%s />%s', in_array($value, $field_settings['options']['default']) ? ' checked="checked"' : '', Sabai::h($label));
        }
        if ($settings['inline']) {
            return implode('&nbsp;&nbsp;', $ret);
        }
        return implode('<br />', $ret);
    }
}