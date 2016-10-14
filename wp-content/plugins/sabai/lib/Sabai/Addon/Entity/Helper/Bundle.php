<?php
class Sabai_Addon_Entity_Helper_Bundle extends Sabai_Helper
{
    public static $bundles = array();
    
    public function help(Sabai $application, $entityOrBundleName)
    {
        $bundle_name = $entityOrBundleName instanceof Sabai_Addon_Entity_IEntity ? $entityOrBundleName->getBundleName() : $entityOrBundleName;
        if (!isset(self::$bundles[$bundle_name])) {
            self::$bundles[$bundle_name] = $application->getModel('Bundle', 'Entity')->name_is($bundle_name)->fetchOne();
        }
        return self::$bundles[$bundle_name];
    }
}