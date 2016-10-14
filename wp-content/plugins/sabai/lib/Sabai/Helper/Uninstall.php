<?php
class Sabai_Helper_Uninstall extends Sabai_Helper
{
    /**
     * Uninstall Sabai
     * @param Sabai $application
     * @param ArrayObject $log
     */
    public function help(Sabai $application, array $addons = null, ArrayObject $log = null)
    {
        if (!isset($log)) $log = new ArrayObject();
        $log[] = 'Uninstalling Sabai...';
        
        $log[] = 'clearing cache...';
        $application->getPlatform()->clearCache();
        $log[] = 'done...';
        
        // Uninstall addons
        $model = $application->getModel('Addon', 'System');
        if (isset($addons)) {
            $model->name_in($addons);
        }
        if ($addons = $model->fetch()) {
            $log[] = 'uninstalling installed addons...';
            foreach ($addons as $addon) {
                $log[] = sprintf('uninstalling %s...', $addon->name);
                try {
                    $application->getAddon($addon->name)->uninstall($log);
                    $log[] = sprintf('%s uninstalled...', $addon->name);
                } catch (Exception $e) {
                    $log[] = sprintf('failed. You must manually uninstall the addon. Error: %s...', $e->getMessage());
                }
            }
        }
    
        $log[] = 'done.';
        return $log;
    }
}
