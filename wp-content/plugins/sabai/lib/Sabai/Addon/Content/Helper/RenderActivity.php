<?php
class Sabai_Addon_Content_Helper_RenderActivity extends Sabai_Helper
{    
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, array $settings = array())
    {
        return $application->Entity_RenderActivity($entity, $settings);
    }
}