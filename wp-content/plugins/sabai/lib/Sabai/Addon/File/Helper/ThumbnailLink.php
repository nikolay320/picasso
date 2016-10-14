<?php
class Sabai_Addon_File_Helper_ThumbnailLink extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $file, array $options = array())
    {
        if (!is_array($file)) return '';
        
        $options += array(
            'rel' => !empty($options['link_entity']) ? '' : 'prettyPhoto',
            'link_image_size' => null,
            'link_entity' => false,
        );
        return sprintf(
            '<a href="%s" class="sabai-file sabai-file-image sabai-file-type-%s %s" rel="%s"><img title="%s" src="%s" alt="" /></a>',
            $options['link_entity'] ? $application->Entity_Url($entity) : (isset($options['url']) ? $options['url'] : $application->File_Url($entity, $file, $options['link_image_size'])),
            Sabai::h($file['extension']),
            isset($options['class']) ? Sabai::h($options['class']) : '',
            isset($options['rel']) ? Sabai::h($options['rel']) : '',
            Sabai::h(isset($options['title']) ? $options['title'] : $file['title']),
            $application->File_ThumbnailUrl($file['name'])
        );
    }
}