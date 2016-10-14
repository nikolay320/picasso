<?php
class Sabai_Addon_Social_FieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array('social_accounts'),
            'default_settings' => array(
                'separator' => ' ',
                'size' => 'medium',
                'target' => '_blank',
                'rel' => array('nofollow', 'external'),
            ),
        );
    }

    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {        
        return array(
            'size' => array(
                '#title' => __('Icon size', 'sabai'),
                '#type' => 'select',
                '#options' => array('' => __('Small', 'sabai'), 'lg' => __('Medium', 'sabai'), '2x' => __('Large', 'sabai'), '3x' => __('X-Large', 'sabai')),
                '#default_value' => $settings['size'],
            ),
            'target' => array(
                '#title' => __('Open link in', 'sabai'),
                '#type' => 'radios',
                '#options' => array(
                    '_self' => __('Current window', 'sabai'),
                    '_blank' => __('New window', 'sabai'),
                ),
                '#class' => 'sabai-form-inline',
                '#default_value' => $settings['target'],
            ),
            'rel' => array(
                '#title' => __('Add to rel attribute', 'sabai'),
                '#class' => 'sabai-form-inline',
                '#type' => 'checkboxes',
                '#options' => array(
                    'nofollow' => __('Add "nofollow"', 'sabai'),
                    'external' => __('Add "external"', 'sabai'),
                ),
                '#default_value' => $settings['rel'],
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $ret = array();
        $medias = $this->_addon->getApplication()->Social_Medias();
        $rel = implode(' ', $settings['rel']);
        $icon_size = $settings['size'] !== '' ? ' fa-' . $settings['size'] : '';
        switch ($settings['size']) {
            case 'lg':
                $image_size = 18.6666666;
                break;
            case '2x':
                $image_size = 28;
                break;
            case '3x':
                $image_size = 42;
                break;
            default:
                $image_size = 14;
        }
        foreach ($values[0] as $media_name => $url) {
            if (!$media = @$medias[$media_name]) continue;
            
            if (isset($media['icon'])) {
                $ret[] = sprintf(
                    '<a target="%s" rel="%s" href="%s" title="%s" style="font-size:14px;"><i class="fa fa-%s%s"></i></a>',
                    $settings['target'],
                    $rel,
                    Sabai::h($url),
                    Sabai::h($media['label']),
                    $media['icon'],
                    $icon_size
                );
            } else {
                $ret[] = sprintf(
                    '<a target="%s" rel="%s" href="%s"><img src="%s" alt="%s" height="%d"/></a>',
                    $settings['target'],
                    $rel,
                    Sabai::h($url),
                    Sabai::h($media['image']),
                    Sabai::h($media['label']),
                    $image_size
                );
            }
        }
        return implode($settings['separator'], $ret);
    }
}
