<?php
class Sabai_Addon_Directory_Controller_Admin_AccessControlSettings extends Sabai_Addon_System_Controller_Admin_AccessControl
{
    protected function _getCategories(Sabai_Context $context)
    {
        return array(
            $this->getAddon()->getListingBundleName(),
            $this->getAddon()->getReviewBundleName(),
            $this->getAddon()->getPhotoBundleName(),
            $this->getAddon()->getLeadBundleName(),
        );
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        parent::submitForm($form, $context);
        
        $allow_existing = false;
        foreach (array_keys($this->_roles) as $role_name) {
            if (in_array($role_name, $this->_adminRoles)) {
                continue;
            }
            if (@$this->_roles[$role_name]->permissions[$this->getAddon()->getListingBundleName() . '_claim']) {
                $allow_existing = true;
                break;
            }
        }

        $claims_config = $this->getAddon()->getConfig('claims');
        if ($claims_config['allow_existing'] !== $allow_existing) {
            $claims_config['allow_existing'] = $allow_existing;
            $this->getAddon()->saveConfig(array('claims' => $claims_config));
            $this->getPlatform()->clearCache();
        }
    }
}