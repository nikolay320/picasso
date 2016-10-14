<?php
class Sabai_Helper_InstallAddon extends Sabai_Helper
{
    public function help(Sabai $application, $addonName, array $config = array(), $priority = 0, ArrayObject $log = null)
    {
        if (!isset($log)) {
            $log = new ArrayObject();
        }
        
        // Check if plugin name is not a reserved name
        $reserved_addon_names = array('Sabai', 'Main', 'Admin', 'Addon', 'Application', 'Model', 'Helper', 'Core', 'Assets', 'Schema', 'Templates', 'Platform', 'Settings', 'Libraries', 'Addons');
        if (in_array(strtolower($addonName), array_map('strtolower', $reserved_addon_names))) {
            throw new Sabai_UnexpectedValueException(sprintf('Add-on name %s is reserved by the system.', $addonName));
        }
        if (!preg_match(Sabai::ADDON_NAME_REGEX, $addonName)) {
            throw new Sabai_UnexpectedValueException(sprintf('Invalid add-on name %s.', $addonName));
        }
        
        $_addon = $application->fetchAddon($addonName, $config);
        
        if (!$_addon->isInstallable()) {
            throw new Sabai_AddonNotInstallableException('Add-on is not installable.');
        }
       
        if ($parent_addon_name = $_addon->hasParent()) {
            // The parent addon must be installed and active
            if (!$application->isAddonLoaded($parent_addon_name)) {
                throw new Sabai_UnexpectedValueException(sprintf('Add-on %s requires %s to be installed and active.', $addonName, $parent_addon_name));
            }
        }
        
        $addon = $application->getModel(null, 'System')->create('Addon')->markNew();
        $addon->name = $_addon->getName();
        $addon->setParams($config + $_addon->getDefaultConfig(), array(), false);
        $addon->version = $_addon->getVersion();
        $addon->priority = $priority >= 99 ? 98 : $priority;
        $addon->events = $_addon->getEvents();
        if (isset($parent_addon_name)) {
            $addon->parent_addon = $parent_addon_name;
        }
        $addon->commit();

        try {
            $_addon->install($log);
        } catch (SabaiFramework_DB_SchemaException $e) {
            try {
                $addon->markRemoved()->commit();
            } catch (SabaiFramework_DB_Exception $e2) {
                $application->LogError($e);
                throw $e2;
            }
            throw $e;
        }
        
        return $addon;
    }
}

