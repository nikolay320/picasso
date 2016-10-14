<?php
class Sabai_Addon_File_Helper_NoImageUrl extends Sabai_Helper
{    
    public function help(Sabai $application, $small = false)
    {
        return $application->NoImageUrl($small);
    }
}