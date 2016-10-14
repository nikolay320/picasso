<?php
class Sabai_Addon_File_Helper_Url extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $file, $size = null)
    {
        if (!is_array($file)) {
            $file = $entity->getSingleFieldValue($file);
        }
        if ($file['is_image'] && $application->getAddon('File')->getConfig('no_pretty_url')) {
            return $application->getAddon('File')->fileStorageGetUrl($file['name'], $size);
        }
        $route = '/file/' . $file['id'] . '/' . $file['title'];
        return $application->Entity_Url($entity, $route, $file['is_image'] && isset($size) ? array('size' => $size) : array());
    }
}