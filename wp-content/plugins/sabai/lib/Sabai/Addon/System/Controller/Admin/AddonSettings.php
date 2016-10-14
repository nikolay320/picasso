<?php
final class Sabai_Addon_System_Controller_Admin_AddonSettings extends Sabai_Addon_System_Controller_Admin_Settings
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $context->setTitle(sprintf(__('Configure %s Add-on', 'sabai'), $context->addon));
        return $this->getAddon($context->addon)->systemGetAdminSettingsForm();
    }
    
    protected function _saveConfig(Sabai_Context $context, array $config)
    {
        $addon = $this->getAddon($context->addon);
        if (is_callable(array($addon, 'systemSaveAdminSettings'))) {
            $addon->systemSaveAdminSettings($config);
        } else {
            $addon->saveConfig($config);
        }
    }
}