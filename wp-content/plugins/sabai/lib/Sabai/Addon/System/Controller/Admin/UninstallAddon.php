<?php
class Sabai_Addon_System_Controller_Admin_UninstallAddon extends Sabai_Addon_Form_Controller
{
    private $_addon;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $addon_name = $context->getRequest()->asStr('addon_name');

        // Fetch addon info from the database
        if (!$this->_addon = $this->getAddon('System')->getModel('Addon')->name_is($addon_name)->fetchOne()) {
            return false;
        }
        
        if (!$this->getAddon($this->_addon->name)->isUninstallable($this->_addon->version)) {
            return false;
        }

        $this->_submitButtons[] = array('#value' => __('Uninstall Add-on', 'sabai'), '#btn_type' => 'danger');
        $message = sprintf(__('Are you sure you want to uninstall <strong>%s</strong>? All data of this addon will be deleted.', 'sabai'), Sabai::h($addon_name));
        return array(
            '#name' => 'system-admin-uninstall-' . strtolower($this->_addon->parent_addon ? $this->_addon->parent_addon : $this->_addon->name),
            '#header' => array('<div class="sabai-alert sabai-alert-danger">' . $message . '</div>'),
            '#addon' => $this->_addon->name,
            '#current_version' => $this->_addon->version,
        );
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $log = new ArrayObject();
        $this->Action('sabai_addon_uninstall', array($this->_addon, $log));
        $this->UninstallAddon($this->_addon, $log);
        $this->reloadAddons(false)->Action('sabai_addon_uninstalled', array($this->_addon, $log));
        $this->getPlatform()->clearCache();
        foreach ($log as $_log) {
            $context->addFlash($_log);
        }
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)))
            ->addFlash(sprintf(__('Add-on %s has been uninstalled.', 'sabai'), $this->_addon->name));
    }
}