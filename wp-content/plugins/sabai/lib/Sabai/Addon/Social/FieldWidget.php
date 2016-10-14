<?php
class Sabai_Addon_Social_FieldWidget extends Sabai_Addon_Field_Widget_AbstractWidget
{   
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Social Accounts', 'sabai'),
            'field_types' => array('social_accounts'),
            'default_settings' => array(
                'medias' => array('facebook', 'twitter', 'googleplus'),
            ),
            'requirable' => false,
            'is_fieldset' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        $options = array();
        foreach ($this->_addon->getApplication()->Social_Medias() as $media_name => $media) {
            if (isset($media['widgetable']) && !$media['widgetable']) continue;
            
            $options[$media_name] = isset($media['icon']) ? sprintf('<i class="fa fa-%s"></i> %s', Sabai::h($media['icon']), Sabai::h($media['label'])) : Sabai::h($media['label']);
        }
        $form = array(
            'medias' => array(
                '#type' => 'checkboxes',
                '#title' => __('Social medias', 'sabai'),
                '#options' => $options,
                '#default_value' => $settings['medias'],
                '#title_no_escape' => true,
                '#class' => 'sabai-form-inline',
            ),
        );
        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $form = array();
        foreach ($this->_addon->getApplication()->Social_Medias() as $media_name => $media) {
            if (!in_array($media_name, $settings['medias'])
                || (isset($media['widgetable']) && !$media['widgetable'])
            ) continue;
            
            $form[$media_name] = array(
                '#type' => 'url',
                '#description' => isset($media['icon']) ? sprintf('<i class="fa fa-%s fa-fw"></i> %s', $media['icon'], Sabai::h($media['label'])) : Sabai::h($media['label']),
                '#default_value' => isset($value[$media_name]) ? $value[$media_name] : null,
                '#title_no_escape' =>true,
                '#regex' => isset($media['regex']) ? $media['regex'] : null,
                '#placeholder' => isset($media['placeholder']) ? $media['placeholder'] : '',
            );
        }
        return $form;
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $html = array();
        foreach ($this->_addon->getApplication()->Social_Medias() as $media_name => $media) {
            if (!in_array($media_name, $settings['medias'])
                || (isset($media['widgetable']) && !$media['widgetable'])
            ) continue;
            
            $html[] = sprintf(
                '<div>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" placeholder="http://" style="width:100%%" /></div>
</div>',
                isset($media['icon']) ? '<i class="fa fa-' . Sabai::h($media['icon']) . '"></i> ' : '',
                Sabai::h($media['label'])
            );
        }
        return implode(PHP_EOL, $html);
    }
    
    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
}
