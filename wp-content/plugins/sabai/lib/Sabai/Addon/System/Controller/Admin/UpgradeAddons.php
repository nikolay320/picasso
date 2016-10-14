<?php
class Sabai_Addon_System_Controller_Admin_UpgradeAddons extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Must be an Ajax request
        if (!$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        // Check request token
        if (!$this->_checkToken($context, 'system_admin_addons')) {
            return;
        }
        
        $log = new ArrayObject();
        $this->UpgradeAddons($context->getRequest()->asArray('addons'), $log);
        foreach ($log as $_log) {
            $context->addFlash($_log);
        }
 
        $context->setSuccess($this->Url('/settings', array('refresh' => 0)));
    }
}