<?php
class Sabai_Helper_CheckAddonVersion extends Sabai_Helper
{
    /**
     * Checks if an addon with a version later than the version specified is installed
     * @param Sabai $application
     * @param type $addonName
     * @param type $addonVersion
     * @return boolean
     */
    public function help(Sabai $application, $addonName, $addonVersion = null, $force = false)
    {
        $addons_to_check = is_array($addonName) ? $addonName : array($addonName => $addonVersion);
        $installed_addons = $application->getInstalledAddons($force);
        foreach ($addons_to_check as $addon_name => $addon_version) {            
            if (empty($installed_addons[$addon_name])) {
                return false;
            }
            if ($addon_version && version_compare($installed_addons[$addon_name]['version'], $addon_version, '<')) {
                return false;
            }
        }
        return true;
    }
}