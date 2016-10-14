<?php
class Sabai_Addon_File_Helper_Link extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, array $file, array $options = array())
    {
        $options += array(
            'class' => '',
            'link_image_size' => null,
            'link_entity' => false,
        );
        return sprintf(
            '<a href="%1$s" title="%2$s" class="sabai-file sabai-file-file sabai-file-type-%3$s %4$s"%5$s>%2$s</a>',
            $options['link_entity'] ? $application->Entity_Url($entity) : $application->File_Url($entity, $file, $options['link_image_size']),
            Sabai::h($file['title']),
            Sabai::h($file['extension']),
            $options['class'] ? Sabai::h($options['class']) : '',
            empty($file['is_image']) ? '' : ' target="_blank" rel="prettyPhoto"'
        );
    }
}