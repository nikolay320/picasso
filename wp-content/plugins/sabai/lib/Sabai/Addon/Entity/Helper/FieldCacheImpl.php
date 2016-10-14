<?php
class Sabai_Addon_Entity_Helper_FieldCacheImpl extends Sabai_Helper
{    
    /**
     * Gets an implementation of Sabai_Addon_Entity_IFieldCache interface
     * @param Sabai $application
     */
    public function help(Sabai $application)
    {
        return $application->getAddon('Entity');
    }
}