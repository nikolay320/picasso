<?php
class Sabai_Helper_LoadCss extends Sabai_Helper
{
    public function help(Sabai $application, $file, $handle, $dependency = null, $package = null, $media = 'screen')
    {
        if ($package !== false) {
            $file = $application->getPlatform()->getAssetsUrl($package) . '/css/' . $file;
        }
        return $application->getPlatform()->addCssFile($file, $handle, $dependency, $media);
    }
}