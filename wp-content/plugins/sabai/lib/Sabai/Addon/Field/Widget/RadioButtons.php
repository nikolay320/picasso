<?php
class Sabai_Addon_Field_Widget_RadioButtons extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Radio buttons', 'sabai'),
            'field_types' => array('choice'),
            'default_settings' => array(
                'inline' => false,
            ),
            'is_fieldset' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        $form = array();
        $form['inline'] = array(
            '#type' => 'checkbox',
            '#title' => __('Display inline', 'sabai'),
            '#description' => __('Check this to align all options on the same line.', 'sabai'),
            '#default_value' => $settings['inline'],
        );

        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        $default_value = isset($value) ? $value : (empty($field_settings['options']['default']) ? null : array_shift($field_settings['options']['default'])); 
        return array(
            '#type' => 'radios',
            '#class' => $settings['inline'] ? 'sabai-form-inline' : null,
            '#options' => $field_settings['options']['options'],
            '#default_value' => $default_value,
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $ret = array();
        $field_settings = $field->getFieldSettings();
        foreach ($field_settings['options']['options'] as $value => $label) {
            $ret[] = sprintf('<input type="radio" disabled="disabled"%s />%s', in_array($value, $field_settings['options']['default']) ? ' checked="checked"' : '', Sabai::h($label));
        }
        if ($settings['inline']) {
            return implode('&nbsp;&nbsp;', $ret);
        }
        return implode('<br />', $ret);
    }
}