<?php
class Sabai_Helper_UpgradeAddon extends Sabai_Helper
{
    public function help(Sabai $application, $addon, array $config = array(), ArrayObject $log = null, $force = false)
    {
        if (!isset($log)) {
            $log = new ArrayObject();
        }
        if (!$addon instanceof Sabai_Addon_System_Model_Addon) {
            if (!$addon = $application->getModel('Addon', 'System')->name_is($addon)->fetchOne()) {
                throw new Sabai_RuntimeException('Failed fetching add-on.');
            }
        }
        $_addon = $application->getAddon($addon->name);
        $new_version = $_addon->getVersion();
        $current_version = $addon->version;
        
        if (!$force
            && !$_addon->isUpgradeable($current_version, $new_version)
        ) {
            return $addon;
        }

        $_addon->upgrade($current_version, $log);
        $addon->version = $new_version;
        $addon->events = $_addon->getEvents();
        $config += $addon->getParams();
        $default_config = $_addon->getDefaultConfig();
        $config += $default_config;
        foreach (array_keys($default_config) as $default_config_name) {
            if (is_array($default_config[$default_config_name])) {
                settype($config[$default_config_name], 'array');
                $config[$default_config_name] += $default_config[$default_config_name];
            }
        }
        $addon->setParams($config, array(), false);
        $addon->commit();
        
        return $addon;
    }
}