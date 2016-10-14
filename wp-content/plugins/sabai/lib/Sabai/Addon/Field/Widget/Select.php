<?php
class Sabai_Addon_Field_Widget_Select extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Select list', 'sabai'),
            'field_types' => array('choice'),
            'accept_multiple' => true,
            'default_settings' => array(),
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        $is_multiple = $field->getFieldMaxNumItems() != 1;
        if (isset($value)) {
            $default_value = array();
            foreach ($value as $_value) {
                $default_value[] = $_value;
            }
        } else {
            $default_value = $field_settings['options']['default']; 
        }
        if (!empty($default_value)) {
            if (!$is_multiple) {
                $default_value = array_shift($default_value);
            }
        } else {
            $default_value = null; 
        }
        $options = $field_settings['options']['options'];
        if (!$is_multiple) {
            $options = array('' => __('- Select -', 'sabai')) + $options;
        }
        return array(
            '#type' => 'select',
            '#options' => $options,
            '#multiple' => $is_multiple,
            '#max_selection' => $field->getFieldMaxNumItems(),
            '#default_value' => $default_value,
            '#empty_value' => '',
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        $is_multiple = $field->getFieldMaxNumItems() != 1;
        if ($is_multiple ) {
            $ret = array(sprintf(
                '<select disabled="disabled" multiple="multiple"%s>',
                sprintf(' size="%d"', (10 < $count = count($field_settings['options'])) ? 10 : $count)
            ));
        } else {
            $ret = array('<select disabled="disabled">', '<option>' . __('- Select -', 'sabai') . '</option>');
        }

        foreach ($field_settings['options']['options'] as $value => $label) {
            $ret[] = sprintf('<option value="%s"%s>%s</option>', Sabai::h($value), in_array($value, $field_settings['options']['default']) ? ' selected="selected"' : '', Sabai::h($label));
        }
        $ret[] = '</select>';
        return implode(PHP_EOL, $ret);
    }
}