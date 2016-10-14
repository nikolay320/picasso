<?php
class Sabai_Addon_WordPress_EditorFieldWidget extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('WordPress editor', 'sabai'),
            'field_types' => array('text', 'markdown_text'),
            'default_settings' => array(
                'rows' => get_option('default_post_edit_rows', 5),
                'no_tinymce' => false,
                'no_quicktags' => false,
            ),
            'is_fieldset' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        return array(
            'no_tinymce' => array(
                '#type' => 'checkbox',
                '#title' => __('Disable Visual mode', 'sabai'),
                '#default_value' => $settings['no_tinymce'],
            ),
            'no_quicktags' => array(
                '#type' => 'checkbox',
                '#title' => __('Disable toolbar in Text mode', 'sabai'),
                '#default_value' => $settings['no_quicktags'],
            ),
            'rows' => array(
                '#type' => 'number',
                '#title' => __('Rows', 'sabai'),
                '#size' => 5,
                '#integer' => true,
                '#default_value' => $settings['rows'],
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
        return array(
            '#type' => 'wordpress_editor',
            '#default_value' => isset($value) ? $value['value'] : null,
            '#rows' => $settings['rows'],
            '#min_length' => isset($field_settings['min_length']) ? $field_settings['min_length'] : @$settings['min_length'],
            '#max_length' => isset($field_settings['max_length']) ? $field_settings['max_length'] : @$settings['max_length'],
            '#no_tinymce' => !empty($settings['no_tinymce']),
            '#no_quicktags' => !empty($settings['no_quicktags']),
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
}