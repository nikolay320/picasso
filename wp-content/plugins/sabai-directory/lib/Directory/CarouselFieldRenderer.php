<?php
class Sabai_Addon_Directory_CarouselFieldRenderer extends Sabai_Addon_Field_Renderer_Carousel
{
    protected $_fieldTypes = array('directory_photos'), $_defaultSettings = array('size' => 'large', 'link' => 'photo', 'link_photo_size' => 'large');

    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'size' => array(
                '#title' => __('Photo size', 'sabai-directory'),
                '#type' => 'radios',
                '#options' => array(
                    'thumbnail' => __('Thumbnail photo', 'sabai-directory'),
                    'medium' => __('Medium size photo', 'sabai-directory'),
                    'large' => __('Large size photo', 'sabai-directory'),
                    '' => __('Original size photo', 'sabai-directory'),
                ),
                '#default_value' => $settings['size'],
                '#weight' => 6,
                '#class' => 'sabai-form-inline',
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
                    'thumbnail' => __('Thumbnail photo', 'sabai-directory'),
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
        ) + parent::fieldRendererGetSettingsForm($fieldType, $settings, $view, $parents);
    }
    
    protected function _getSlides(array $settings, array $values, Sabai_Addon_Entity_IEntity $entity, $id)
    {
        $ret = array();  
        $app = $this->_addon->getApplication();
        if ($settings['link'] === 'none') {
            foreach ($values as $photo) {
                $ret[] = sprintf(
                    '<img src="%s" title="%s" alt=""%s />',
                    $app->Directory_PhotoUrl($photo, $settings['size']),
                    Sabai::h($photo->getTitle()),
                    empty($settings['itemprop']) ? '' : ' itemprop="image"'
                );
            }
        } elseif ($settings['link'] === 'photo') {
            foreach ($values as $photo) {
                $ret[] = sprintf(
                    '<a href="%s" rel="prettyPhoto[%s]"><img title="%s" src="%s" alt=""%s /></a>',
                    $app->Directory_PhotoUrl($photo, $settings['link_photo_size']),
                    $id,
                    Sabai::h($photo->getTitle()),
                    $app->Directory_PhotoUrl($photo, $settings['size']),
                    empty($settings['itemprop']) ? '' : ' itemprop="image"'
                );
            }
        } else {
            $url = $app->Entity_Url($entity);
            foreach ($values as $photo) {
                $ret[] = sprintf(
                    '<a href="%s"><img title="%s" src="%s" alt=""%s /></a>',
                    $url,
                    Sabai::h($photo->getTitle()),
                    $app->Directory_PhotoUrl($photo, $settings['size']),
                    empty($settings['itemprop']) ? '' : ' itemprop="image"'
                );
            }
        }
        return $ret;
    }
}