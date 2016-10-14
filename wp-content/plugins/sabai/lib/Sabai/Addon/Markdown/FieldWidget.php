<?php
class Sabai_Addon_Markdown_FieldWidget extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Markdown editor', 'sabai'),
            'field_types' => array('text', 'markdown_text'),
            'default_settings' => array(
                'rows' => 5,
                'hide_buttons' => false,
                'hide_preview' => false,
            ),
            'is_fieldset' => true,
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
            'hide_buttons' => array(
                '#type' => 'checkbox',
                '#title' => __('Hide editor buttons', 'sabai'),
                '#default_value' => $settings['hide_buttons'],
            ),
            'hide_preview' => array(
                '#type' => 'checkbox',
                '#title' => __('Hide preview', 'sabai'),
                '#default_value' => $settings['hide_preview'],
            ),
        );
    }
    
    public function isIframeUrlsRequired($form, $parents)
    {
        $values = $form->getValue($parents);
        return !empty($values['iframe']);
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $field_settings = $field->getFieldSettings();
        $config = $this->_addon->getConfig();
        return array(
            '#type' => 'markdown_textarea',
            '#rows' => $settings['rows'],
            '#default_value' => isset($value) ? $value['value'] : null,
            '#help_url' => !empty($config['help']) ? $config['help_url'] : null,
            '#help_window_w' => $config['help_window']['width'],
            '#help_window_h' => $config['help_window']['height'],
            '#hide_buttons' => !empty($settings['hide_buttons']),
            '#hide_preview' => !empty($settings['hide_preview']),
            '#min_length' => isset($field_settings['min_length']) ? $field_settings['min_length'] : @$settings['min_length'],
            '#max_length' => isset($field_settings['max_length']) ? $field_settings['max_length'] : @$settings['max_length'],
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $value = $field->getFieldDefaultValue();
        return sprintf('<textarea rows="%d" disabled="disabled" style="width:100%%;">%s</textarea>', $settings['rows'], Sabai::h($value[0]));
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
    
    public function fieldWidgetHtmlizeText(Sabai_Addon_Field_IField $field, array $settings, $value, Sabai_Addon_Entity_IEntity $entity)
    {
        return $this->_addon->getApplication()->Htmlize($this->_addon->getApplication()->Markdown_Transform($value), false);
    }
}