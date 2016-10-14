<?php
class Sabai_Addon_Entity_Helper_Query extends Sabai_Helper
{    
    public function help(Sabai $application, $entityType = 'content', $operator = 'AND')
    {
        return new Sabai_Addon_Entity_Query($application->getAddon('Entity'), $entityType, $operator);
    }
}