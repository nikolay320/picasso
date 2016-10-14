<?php
class Sabai_Helper_JsUrl extends Sabai_Helper
{
    public function help(Sabai $application, $file, $package = null)
    {       
        return $application->getPlatform()->getAssetsUrl($package) . '/js/' . $file;
    }
}