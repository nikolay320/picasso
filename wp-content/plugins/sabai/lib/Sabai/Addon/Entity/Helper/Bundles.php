<?php
class Sabai_Addon_Entity_Helper_Bundles extends Sabai_Helper
{
    public function help(Sabai $application, array $bundleNames)
    {
        $ret = array();
        foreach ($bundleNames as $k => $bundle_name) {
            if (isset(Sabai_Addon_Entity_Helper_Bundle::$bundles[$bundle_name])) {
                $ret[$bundle_name] = Sabai_Addon_Entity_Helper_Bundle::$bundles[$bundle_name];
                unset($bundleNames[$k]);
            }
        }
        if (!empty($bundleNames)) {
            foreach ($application->getModel('Bundle', 'Entity')->name_in($bundleNames)->fetch() as $bundle) {
                if (!$application->isAddonLoaded($bundle->addon)) continue;
                
                Sabai_Addon_Entity_Helper_Bundle::$bundles[$bundle->name] = $ret[$bundle->name] = $bundle;
            }
        }
        return $ret;
    }
}