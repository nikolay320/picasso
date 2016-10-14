<?php
class Sabai_Helper_SitePath extends Sabai_Helper
{
    protected $_sitePath;
    
    public function help(Sabai $application)
    {
        if (!isset($this->_sitePath)) {
            $this->_sitePath = $application->Path($application->getPlatform()->getSitePath());
        }
        return $this->_sitePath;
    }
}