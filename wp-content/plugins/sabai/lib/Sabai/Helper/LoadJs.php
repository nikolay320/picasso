<?php
class Sabai_Helper_LoadJs extends Sabai_Helper
{    
    public function help(Sabai $application, $file, $handle, $dependency = null, $package = null)
    {
        if ($package !== false) {
            $file = $application->getPlatform()->getAssetsUrl($package) . '/js/' . $file;
        }
        $application->getPlatform()->addJsFile($file, $handle, $dependency);
    }
}