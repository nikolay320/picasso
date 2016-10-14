<?php
class Sabai_Addon_Directory_SocialFieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array($this->_name),
            'default_settings' => array(
                'rel' => array('nofollow', 'external'),
            ),
        );
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'rel' => array(
                '#class' => 'sabai-form-inline',
                '#type' => 'checkboxes',
                '#options' => array(
                    'nofollow' => __('Add rel="nofollow"', 'sabai-directory'),
                    'external' => __('Add rel="external"', 'sabai-directory'),
                ),
                '#default_value' => $settings['rel'],
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $ret = array();
        $value = $values[0];
        $rel = implode(' ', $settings['rel']);
        if (!empty($value['twitter'])) {
            $ret[] = '<a class="sabai-directory-social-twitter" target="_blank" rel="'. $rel .'" href="http://twitter.com/' . Sabai::h($value['twitter']) . '"><i class="fa fa-twitter-square"></i></a>';
        }
        if (!empty($value['facebook'])) {
            $ret[] = '<a class="sabai-directory-social-facebook" target="_blank" rel="'. $rel .'" href="' . Sabai::h($value['facebook']) . '"><i class="fa fa-facebook-square"></i></a>';
        }
        if (!empty($value['googleplus'])) {
            $ret[] = '<a class="sabai-directory-social-googleplus" target="_blank" rel="'. $rel .'" href="' . Sabai::h($value['googleplus']) . '"><i class="fa fa-google-plus-square"></i></a>';
        }
        return implode(PHP_EOL, $ret);
    }
}