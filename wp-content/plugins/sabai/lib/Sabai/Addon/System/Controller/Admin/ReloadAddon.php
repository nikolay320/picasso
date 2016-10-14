<?php
class Sabai_Addon_System_Controller_Admin_ReloadAddon extends Sabai_Addon_Form_Controller
{
    private $_addon;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $addon_name = $context->getRequest()->asStr('addon_name');

        // Fetch addon info from the database
        if (!$this->_addon = $this->getAddon('System')->getModel('Addon')->name_is($addon_name)->fetchOne()) {
            return false;
        }

        $this->_submitButtons[] = array('#value' => __('Reload Add-on', 'sabai'), '#btn_type' => 'info');
        $message = sprintf(__('Press the button below to reload the current version of <strong>%s</strong>.', 'sabai'), Sabai::h($addon_name));
        $form = array(
            '#name' => 'system-admin-upgrade-' . strtolower($this->_addon->parent_addon ? $this->_addon->parent_addon : $this->_addon->name),
            '#header' => array('<div>' . $message . '</div>'),
            '#addon' => $this->_addon->name,
            '#current_version' => $this->_addon->version,
            '#upgrade_version' => $this->_addon->version,
            'config' => array(
                '#tree' => true,
                '#tree_allow_override' => false,
            ),
        );
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $log = new ArrayObject();
        $current_version = $this->_addon->version;
        $this->Action('sabai_addon_reload', array($this->_addon, $log));
        $this->UpgradeAddon($this->_addon, array(), $log);
        $this->reloadAddons()->Action('sabai_addon_upgraded', array($this->_addon, $current_version, $log));
        $this->getPlatform()->clearCache();
        foreach ($log as $_log) {
            $context->addFlash($_log);
        }
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)))
            ->addFlash(sprintf(__('Add-on %s has been upgraded.', 'sabai'), $this->_addon->name));
    }
}