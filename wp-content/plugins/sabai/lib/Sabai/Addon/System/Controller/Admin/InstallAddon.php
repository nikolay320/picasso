<?php
class Sabai_Addon_System_Controller_Admin_InstallAddon extends Sabai_Addon_Form_Controller
{
    private $_addonName;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_addonName = $context->getRequest()->asStr('addon_name');
        
        // Fetch addon info from the file system
        $local_addons = $this->getLocalAddons(true);
        if (!isset($local_addons[$this->_addonName])) {
            return false;
        }
        $addon_local = $local_addons[$this->_addonName];

        $this->_submitButtons[] = array('#value' => __('Install Add-on', 'sabai'), '#btn_type' => 'success');

        $form = array(
            '#name' => 'system-admin-install-' . strtolower(isset($addon_local['parent']) ? $addon_local['parent'] : $this->_addonName),
            '#header' => array('<div>' . sprintf(__('You are about to install the <strong>%s</strong> (version: <strong>%s</strong>) add-on.', 'sabai'), Sabai::h($this->_addonName), Sabai::h($addon_local['version'])) . '</div>'),
            '#addon' => $this->_addonName,
            '#install_version' => $addon_local['version'],
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
        $addon = $this->InstallAddon($this->_addonName, array());
        $this->reloadAddons(); // Refresh addons to include the installed addon during the installed event
        $this->Action('sabai_addon_installed', array($addon, $log));
        $this->getPlatform()->clearCache();        
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)))
            ->addFlash(sprintf(__('Add-on %s has been installed.', 'sabai'), $this->_addonName));
    }
}