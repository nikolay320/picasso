<?php
class Sabai_Addon_WordPress_Controller_Admin_VerifyLicense extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if ((!$plugin = $context->getRequest()->asStr('plugin', false))
            || (!$license_type = $context->getRequest()->asStr('license_type', false))
            || (!$license_key = $context->getRequest()->asStr('license_key', false))
        ) {
            $context->setBadRequestError();
            return;
        }

        // Check request token
        if (!$this->_checkToken($context, 'wordpress_verify_license')) return;
        
        $remote_args = array(
            'license_type' => $license_type,
            'license_key' => $license_key,
        );
        require_once 'Sabai/Platform/WordPress/AutoUpdater.php';
        $updater = new Sabai_Platform_WordPress_AutoUpdater($plugin, $remote_args);
        
        if (false === $info = $updater->getRemoteInfo()) {
            $context->setError($updater->getRemoteError());
            return;
        }
        
        if (empty($info->download_link)) {
            $context->setError('Invalid license.');
            return;
        }
        
        $context->setSuccess()->setSuccessAttributes((array)$info);
    } 
}