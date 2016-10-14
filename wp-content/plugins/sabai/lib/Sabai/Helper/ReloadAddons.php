<?php
class Sabai_Helper_ReloadAddons extends Sabai_Helper
{
    public function help(Sabai $application, array $addons, ArrayObject $log = null)
    {
        if (empty($addons)) return;
        
        if (!isset($log)) {
            $log = new ArrayObject();
        }
        $addon_entities = $addon_versions = array();
        foreach ($application->getModel('Addon', 'System')->name_in($addons)->fetch() as $addon) {
            $addon_versions[$addon->name] = $addon->version;
            $addon_entities[$addon->name] = $application->UpgradeAddon($addon, array(), $log);
        }
        $application->reloadAddons();
        foreach ($addon_entities as $addon_name => $addon_entity) {
            $application->Action('sabai_addon_upgraded', array($addon_entity, $addon_versions[$addon_name], $log));
        }
        $application->getPlatform()->clearCache();
    }
}