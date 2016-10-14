<?php
class Sabai_Addon_System_Controller_Admin_ClearCache extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Must be an Ajax request
        if (!$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        // Check request token
        if (!$this->_checkToken($context, 'system_admin_settings', true)) {
            return;
        }
        
        $this->System_ClearCache();

        // Send success response
        $context->setSuccess('/settings');
    }
}