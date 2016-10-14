<?php
class Sabai_Addon_File_Helper_Delete extends Sabai_Helper
{
    public function help(Sabai $application, $fileName)
    {        
        return $application->getAddon('File')->getStorage()->fileStorageDelete($fileName);
    }
}