<?php
class Sabai_Helper_FileUrl extends Sabai_Helper
{
    public function help(Sabai $application, $file, $addon = null)
    {     
        if (isset($addon)) {
            $file = $application->getAddonPath($addon) . '/' . $file;
        }   
        return str_replace($application->SitePath(), $application->getPlatform()->getSiteUrl(), $file);
    }
}