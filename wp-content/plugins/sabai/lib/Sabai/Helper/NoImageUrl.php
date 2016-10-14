<?php
class Sabai_Helper_NoImageUrl extends Sabai_Helper
{
    protected $image, $_smallImage;
    
    public function help(Sabai $application, $small = false)
    {
        if ($small) {
            if (!isset($this->_smallImage)) {
                if (!$this->_smallImage = $application->getAddon('System')->getConfig('no_image_small')) {
                    $this->_smallImage = $application->getPlatform()->getAssetsUrl() . '/images/no_image_small.png';
                }
            }
            return $this->_smallImage;
        }
        if (!isset($this->_image)) {
            if (!$this->_image = $application->getAddon('System')->getConfig('no_image')) {
                $this->_image = $application->getPlatform()->getAssetsUrl() . '/images/no_image.png';
            }
        }
        return $this->_image;
    }
}