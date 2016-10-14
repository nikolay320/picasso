<?php
class Sabai_Addon_File_Helper_ThumbnailUrl extends Sabai_Helper
{
    public function help(Sabai $application, $fileName)
    {
        if (is_array($fileName)) {
            $fileName = isset($fileName[0]) ? $fileName[0]['name'] : $fileName['name'];
        }
        return $application->getAddon('File')->fileStorageGetThumbnailUrl($fileName);
    }
}