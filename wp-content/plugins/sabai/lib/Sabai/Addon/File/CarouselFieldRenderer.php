<?php
class Sabai_Addon_File_CarouselFieldRenderer extends Sabai_Addon_Field_Renderer_Carousel
{
    protected $_fieldTypes = array('file_image'), $_defaultSettings = array('size' => 'large', 'link_post' => false);

    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'size' => array(
                '#title' => __('Photo size', 'sabai'),
                '#type' => 'radios',
                '#options' => array(
                    'thumbnail' => __('Thumbnail photo', 'sabai'),
                    'medium' => __('Medium size photo', 'sabai'),
                    'large' => __('Large size photo', 'sabai'),
                    '' => __('Original size photo', 'sabai'),
                ),
                '#default_value' => $settings['size'],
                '#weight' => 6,
                '#class' => 'sabai-form-inline',
            ),
            'link_post' => array(
                '#type' => 'checkbox',
                '#title' => __('Link to post', 'sabai'),
                '#default_value' => !empty($settings['link_post']),
                '#weight' => 99
            ),
        ) + parent::fieldRendererGetSettingsForm($fieldType, $settings, $view, $parents);
    }
    
    protected function _getSlides(array $settings, array $values, Sabai_Addon_Entity_IEntity $entity, $id)
    {
        $ret = array();  
        $app = $this->_addon->getApplication();
        if (!$settings['link_post']) {
            foreach ($values as $value) {
                $ret[] = sprintf(
                    '<a href="%s" rel="prettyPhoto[%s]"><img title="%s" src="%s" alt="" /></a>',
                    $app->File_Url($entity, $value, 'large'),
                    $id,
                    Sabai::h($value['title']),
                    $settings['size'] === 'thumbnail' ? $app->File_ThumbnailUrl($value) : $app->File_Url($entity, $value, $settings['size'])
                );
            }
        } else {
            $url = $app->Entity_Url($entity);
            foreach ($values as $value) {
                $ret[] = sprintf(
                    '<a href="%s"><img title="%s" src="%s" alt="" /></a>',
                    $url,
                    Sabai::h($value['title']),
                    $settings['size'] === 'thumbnail' ? $app->File_ThumbnailUrl($value) : $app->File_Url($entity, $value, $settings['size'])
                );
            }
        }
        return $ret;
    }
}