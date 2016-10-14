<?php
class Sabai_Addon_Directory_Helper_PhotoUrl extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $size = null)
    {
        return $size === 'thumbnail'
            ? $application->File_ThumbnailUrl($entity->file_image[0]['name'])
            : $application->File_Url($entity, $entity->file_image[0], $size);
    }
}