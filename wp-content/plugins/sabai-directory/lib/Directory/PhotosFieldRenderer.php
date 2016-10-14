<?php
class Sabai_Addon_Directory_PhotosFieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected static $_jsLoaded;
    
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array($this->_name),
            'separatable' => false,
            'default_settings' => array(
                'feature' => true,
                'feature_size' => 'large',
                'thumbnail' => true,
                'cols' => 4,
                'max_num' => 0,
                'link' => 'photo',
                'link_photo_size' => 'large',
                'hidden_xs' => true,
                'separator' => PHP_EOL,
            ),
        );
    }
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        $form = array(
            'feature' => array(
                '#title' => __('Show first photo as featured', 'sabai-directory'),
                '#type' => 'checkbox',
                '#default_value' => !empty($settings['feature']),
            ),
            'feature_size' => array(
                '#title' => __('Featured photo size', 'sabai-directory'),
                '#type' => 'radios',
                '#options' => array(
                    'thumbnail' => __('Thumbnail photo', 'sabai-directory'),
                    'medium' => __('Medium size photo', 'sabai-directory'),
                    'large' => __('Large size photo', 'sabai-directory'),
                    '' => __('Original size photo', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#default_value' => $settings['feature_size'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[feature][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
            'link' => array(
                '#type' => 'radios',
                '#title' => __('Link photos to:', 'sabai-directory'),
                '#options' => array(
                    'none' => __('Do not link', 'sabai-directory'),
                    'page' => __('Single listing page', 'sabai-directory'),
                    'photo' => __('Single photo', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#default_value' => $settings['link'],
                '#weight' => 99,
            ),
            'link_photo_size' => array(
                '#title' => __('Linked photo size', 'sabai-directory'),
                '#type' => 'radios',
                '#options' => array(
                    'medium' => __('Medium size photo', 'sabai-directory'),
                    'large' => __('Large size photo', 'sabai-directory'),
                    '' => __('Original size photo', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
                '#default_value' => $settings['link_photo_size'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[link]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'photo'),
                    ),
                ),
                '#weight' => 100,
            ),
        );
        if (!in_array($view, array('map', 'grid'))) {
            $form += array(
                'thumbnail' => array(
                    '#title' => __('Show thumbnail photos', 'sabai-directory'),
                    '#type' => 'checkbox',
                    '#default_value' => !empty($settings['thumbnail']),
                ),
                'cols' => array(
                    '#title' => __('Number of thumbnail columns', 'sabai-directory'),
                    '#type' => 'radios',
                    '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6, 12 => 12),
                    '#default_value' => $settings['cols'],
                    '#class' => 'sabai-form-inline',
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[thumbnail][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                ),
                'max_num' => array(
                    '#title' => __('Maximum number of thumbnails to display (0 for unlimited)', 'sabai-directory'),
                    '#type' => 'number',
                    '#default_value' => $settings['max_num'],
                    '#size' => 5,
                    '#integer' => true,
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[thumbnail][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                    '#min_value' => 0,
                ),
                'hidden_xs' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Hide thumbnails on small-screen devices', 'sabai-directory'),
                    '#default_value' => $settings['hidden_xs'],
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[thumbnail][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                ),    
            );
        } else {
            $form += array(
                'thumbnail' => array(
                    '#type' => 'hidden',
                    '#value' => 0,
                ),
            );
        }
      
        return $form;
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $application = $this->_addon->getApplication();
        
        $ret = array('<div class="sabai-directory-photos">');
        if ($settings['feature']) {
            $photo = $values[0];
            if ($settings['link'] === 'none') {
                $ret[] = sprintf(
                    '<img src="%s" alt="%s"%s />',
                    $application->Directory_PhotoUrl($photo, $settings['feature_size']),
                    Sabai::h($photo->getTitle()),
                    empty($settings['itemprop']) ? '' : ' itemprop="image"'
                );
            } else {
                $ret[] = sprintf(
                    '<a href="%s" rel="%s"><img src="%s" alt="%s"%s /></a>',
                    $settings['link'] === 'photo' ? $application->Directory_PhotoUrl($photo, $settings['link_photo_size']) : $application->Entity_Url($entity),
                    $settings['link'] === 'photo' ? 'prettyPhoto' : '',
                    $application->Directory_PhotoUrl($photo, $settings['feature_size']),
                    Sabai::h($photo->getTitle()),
                    empty($settings['itemprop']) ? '' : ' itemprop="image"'
                );
            }
        }
        if ($settings['thumbnail'] && (!$settings['feature'] || count($values) > 1)) {
            // Load Js files
            if (!self::$_jsLoaded) {
                $application->LoadJs('sabai-directory.min.js', 'sabai-directory', array('sabai'), 'sabai-directory');
                self::$_jsLoaded = true;
            }
            
            if (!empty($settings['max_num'])) {
                $values = array_slice($values, 0, $settings['max_num']);
            }
            $cols = 12 / $settings['cols'];
            $i = 0;
            $ret[] = $settings['hidden_xs'] ? '<div class="sabai-directory-thumbnails sabai-hidden-xs">' : '<div class="sabai-directory-thumbnails">';
            while ($photos = array_slice($values, $i * $settings['cols'], $settings['cols'])) {
                $ret[] = '<div class="sabai-row">';
                foreach ($photos as $photo) {
                    $photo_url = $application->Directory_PhotoUrl($photo, $settings['link_photo_size']);
                    if ($settings['link'] === 'none') {
                        $ret[] = sprintf(
                            '<div class="sabai-col-sm-%d sabai-col-xs-4"><img src="%s" data-full-image="%s" alt="%s" /></div>',
                            $cols,
                            $application->Directory_PhotoUrl($photo, 'thumbnail'),
                            $photo_url,
                            Sabai::h($photo->getTitle())
                        );
                    } else {
                        $ret[] = sprintf(
                            '<div class="sabai-col-sm-%d sabai-col-xs-4"><a href="%s" rel="%s"><img src="%s" data-full-image="%s" alt="%s" /></a></div>',
                            $cols,
                            $settings['link'] === 'photo' ? $photo_url : $application->Entity_Url($entity),
                            $settings['link'] === 'photo' ? 'prettyPhoto[' . $entity->getBundleName() . $entity->getId() . ']' : '',
                            $application->Directory_PhotoUrl($photo, 'thumbnail'),
                            $photo_url,
                            Sabai::h($photo->getTitle())
                        );
                    }
                }
                $ret[] = '</div>';
                ++$i;
            }
            $ret[] = '</div>';
        }
        $ret[] = '</div>';
        return implode($settings['separator'], $ret);
    }
}
