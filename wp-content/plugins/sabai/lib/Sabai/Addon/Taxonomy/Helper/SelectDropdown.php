<?php
class Sabai_Addon_Taxonomy_Helper_SelectDropdown extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, array $options = array())
    {
        return $application->Taxonomy_SelectList($bundleName, $options);
    }
}