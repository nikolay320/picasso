<?php
class Sabai_Addon_Field_Widget_Video extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Video field', 'sabai'),
            'field_types' => array('video'),
            'default_settings' => array(),
            'repeatable' => array('group_fields' => false),
            'is_fieldset' => true,
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        return array(
            '#type' => 'fieldset',
            '#class' => 'sabai-form-group',
            'provider' => array(
                '#type' => 'radios',
                '#options' => array('youtube' => '<i class="fa fa-youtube-square"></i> YouTube', 'vimeo' => '<i class="fa fa-vimeo-square"></i> Vimeo'),
                '#default_value' => isset($value['provider']) ? $value['provider'] : 'youtube',
                '#class' => 'sabai-form-inline',
                '#title_no_escape' => true,
            ),
            'id' => array(
                '#field_prefix' => __('Numéro de la vidéo:', 'sabai'),
                '#type' => 'textfield',
                '#default_value' => isset($value['id']) ? $value['id'] : null,
                '#size' => 20,
            ),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return sprintf('
<div>
    <div>
        <input type="radio" disabled="disabled" /> YouTube
        <input type="radio" disabled="disabled" /> Vimeo
    </div>
</div>
<div>
    <div><span class="sabai-form-field-prefix">%1$s</span><input type="textfield" disabled="disabled" /></div>
</div>',
            __('Video ID:', 'sabai')
        );
    }
}