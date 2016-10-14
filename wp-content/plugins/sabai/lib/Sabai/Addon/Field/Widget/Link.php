<?php
class Sabai_Addon_Field_Widget_Link extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Link field', 'sabai'),
            'field_types' => array('link'),
            'default_settings' => array(
                'size' => 'large',
                'title' => array('default' => null, 'no_custom' => false),
            ),
            'repeatable' => array('group_fields' => false),
            'is_fieldset' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        $form = array(
            'size' => array(
                '#type' => 'select',
                '#title' => __('Field size', 'sabai'),
                '#options' => array(
                    'small' => __('Small', 'sabai'),
                    'medium' => __('Medium', 'sabai'),
                    'large' => __('Large (responsive)', 'sabai'),
                ),
                '#default_value' => $settings['size'],
            ),
            'title' => array(
                '#title' => __('Default link title', 'sabai'),
                '#collapsible' => false,
                'default' => array(
                    '#type' => 'textfield',
                    '#default_value' => @$settings['title']['default'],
                ),
                'no_custom' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Do not allow custom link title', 'sabai'),
                    '#default_value' => @$settings['title']['no_custom'],
                ),
            ),
        );
        
        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        $form = array(
            '#type' => 'fieldset',
            '#class' => 'sabai-form-group',
            'url' => array(
                '#type' => 'textfield',
                '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
                '#default_value' => isset($value['url']) ? $value['url'] : null,
                '#char_validation' => 'url',
                '#attributes' => array('placeholder' => 'http://'),
                '#weight' => 1,
            ),
            'title' => array(
                '#field_prefix' => __('Link Title:', 'sabai'),
                '#type' => 'textfield',
                '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
                '#default_value' => isset($value['title']) ? $value['title'] : @$settings['title']['default'],
                '#weight' => 3,
                '#required' => false,
            ),
        );
        if (!empty($settings['title']['no_custom'])) {
            $form['title']['#type'] = 'hidden';
        }

        return $form;
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        $size_html = isset($settings['size']) && isset($sizes[$settings['size']]) ? sprintf('size="%d"', $sizes[$settings['size']]) : 'style="width:100%;"';
        if (!empty($settings['title']['no_custom'])) {
            return sprintf('<input type="textfield" disabled="disabled" %1$s placeholder="http://" />', $size_html);
        }
        return sprintf(
            '<div>
    <div><input type="textfield" disabled="disabled" %2$s placeholder="http://" %3$s /></div>
</div>
<div>
    <div><span class="sabai-form-field-prefix">%1$s</span><input type="textfield" disabled="disabled" value="%2$s" %3$s /></div>
</div>',
            __('Link Title:', 'sabai'),
            Sabai::h($settings['title']['default']),
            $size_html
        );
    }
}