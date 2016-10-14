<?php
class Sabai_Addon_Entity_Helper_BundleCustomFields extends Sabai_Helper
{   
    /**
     * Returns all custom fields of a bundle 
     * @param Sabai $application
     * @param string $bundleName
     */
    public function help(Sabai $application, $bundleName)
    {
        $ret = array();
        foreach ($application->Entity_Field($bundleName) as $field) {
            if (!$field->isCustomField()) continue;
            
            $ret[] = $field;
        }
        return $ret;
    }
}