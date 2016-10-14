<?php
class Sabai_Addon_File_FieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected $_count = 0;
    
    protected function _fieldRendererGetInfo()
    {
         switch ($this->_name) {
            case 'file_file':
                return array(
                    'field_types' => array('file_file'),
                    'default_settings' => array('separator' => ' '),
                );
            case 'file_image':
                return array(
                    'field_types' => array('file_image'),
                    'default_settings' => array(
                        'size' => 'thumbnail',
                        'cols' => 4,
                        'separator' => PHP_EOL,
                        'link_post' => false,
                    ),
                    'separatable' => false,
                );
         }
    }

    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        switch ($this->_name) {
            case 'file_image':
                return array(
                    'size' => array(
                        '#title' => __('Image size', 'sabai'),
                        '#type' => 'radios',
                        '#options' => array(
                            'thumbnail' => __('Thumbnail', 'sabai'),
                            'medium' => __('Medium size', 'sabai'),
                            'large' => __('Large size', 'sabai'),
                            '' => __('Original size', 'sabai'),
                        ),
                        '#class' => 'sabai-form-inline',
                        '#default_value' => $settings['size'],
                    ),
                    'cols' => array(
                        '#title' => __('Number of columns', 'sabai'),
                        '#type' => 'radios',
                        '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6, 12 => 12),
                        '#default_value' => $settings['cols'],
                        '#class' => 'sabai-form-inline',
                    ),
                    'link_post' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Link to post', 'sabai'),
                        '#default_value' => !empty($settings['link_post']),
                    ),
                );                
         }
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        switch ($this->_name) {
            case 'file_file':
                $ret = array();
                foreach ($values as $value) {
                    $ret[] = '<i class="fa ' . $this->_addon->getApplication()->File_Icon($value['extension']) . '"></i> ' . $this->_addon->getApplication()->File_Link($entity, $value);
                }
                return implode($settings['separator'], $ret);
            case 'file_image':
                $ret = array('<div class="sabai-row" style="margin-left:0; margin-right:0;">');
                $cols = 12 / $settings['cols'];
                $rel = 'prettyPhoto[sabai-field-images-' . $this->_count++ . ']';
                foreach ($values as $image) {
                    $image_url = $this->_getImageUrl($entity, $image, $settings['size']);
                    $full_image_url = $settings['size'] == '' ? $image_url : $this->_getImageUrl($entity, $image, '');
                    $ret[] = sprintf(
                        '<div class="sabai-col-sm-%d sabai-col-xs-4" style="padding:0;"><a href="%s" rel="%s"><img src="%s" data-full-image="%s" title="%s" alt="" /></a></div>',
                        $cols,
                        $settings['link_post']
                            ? $this->_addon->getApplication()->Entity_Url($entity)
                            : ($settings['size'] == 'large' ? $image_url : $this->_getImageUrl($entity, $image, 'large')),
                        $settings['link_post'] ? '' : $rel,
                        $image_url,
                        $full_image_url,
                        Sabai::h($image['title'])
                    );
                }
                $ret[] = '</div>';
                return implode(PHP_EOL, $ret);
        }
    }
    
    protected function _getImageUrl($entity, $image, $size)
    {
        return $size === 'thumbnail'
            ? $this->_addon->getApplication()->File_ThumbnailUrl($image['name'])
            : $this->_addon->getApplication()->File_Url($entity, $image, $size);
    }
}