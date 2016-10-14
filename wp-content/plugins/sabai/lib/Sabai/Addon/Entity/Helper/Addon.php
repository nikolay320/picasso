<?php
class Sabai_Addon_Entity_Helper_Addon extends Sabai_Helper
{
    public function help(Sabai $application, $entityOrBundleName)
    {
        return $application->getAddon($application->Entity_Bundle($entityOrBundleName)->addon);
    }
}