<?php
class Sabai_Helper_PackagePath extends Sabai_Helper
{
    protected $_path;
    
    public function help(Sabai $application)
    {
        if (!isset($this->_path)) {
            $this->_path = $application->Path($application->getPlatform()->getPackagePath());
        }
        return $this->_path;
    }
}