<?php
class Sabai_Addon_Entity_Helper_BundleCollection extends Sabai_Addon_Entity_Helper_Bundle
{
    public function help(Sabai $application, $bundleNames)
    {
        $ret = array();
        foreach ($bundleNames as $k => $bundle_name) {
            if (isset($this->_bundles[$bundle_name])) {
                $ret[$this->_bundles[$bundle_name]->id] = $this->_bundles[$bundle_name];
                unset($bundleNames[$k]);
            }
        }
        if (!empty($bundleNames)) {
            foreach ($application->getModel('Bundle', 'Entity')->name_in($bundleNames)->fetch() as $bundle) {
                if (!$application->isAddonLoaded($bundle->addon)) continue;
                
                $this->_bundles[$bundle->name] = $ret[$bundle->id] = $bundle;
            }
        }
        return $application->getModel('Bundle', 'Entity')->createCollection($ret);
    }
}