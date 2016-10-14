<?php
class Sabai_Helper_ImageUrl extends Sabai_Helper
{
    public function help(Sabai $application, $file, $package = null)
    {       
        return $application->getPlatform()->getAssetsUrl($package) . '/images/' . $file;
    }
}