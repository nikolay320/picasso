<?php
require_once SABAI_PACKAGE_PAIDLISTINGS_PATH . '/lib/PaidListings/Controller/Order.php';

class Sabai_Addon_PaidDirectoryListings_Controller_RenewListing extends Sabai_Addon_PaidListings_Controller_Order
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if ($form = parent::_doGetFormSettings($context, $formStorage)) {
            $form['listing_id'] = array('#type' => 'hidden', '#value' => $context->entity->getId());
        }
        $this->_cancelUrl = '/' . $this->getAddon('Directory')->getSlug('dashboard');
        return $form;
    }
    
    protected function _getPlanType(Sabai_Context $context)
    {
        return 'base';
    }
    
    protected function _getEntity(Sabai_Context $context)
    {
        return $context->entity;
    }
    
    protected function _getOrderMeta(Sabai_Context $context)
    {
        return array('paiddirectorylistings_claim' => array('claim_renew' => true));
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {
        parent::_complete($context, $formStorage);
        $context->setSuccessUrl($this->_cancelUrl);
    }
    
    protected function _getPaymentReturnUrl(Sabai_Context $context, array $formStorage)
    {
        $url = parent::_getPaymentReturnUrl($context, $formStorage);
        $url->params = array('listing_id' => $context->entity->getId()) + $url->params;
        return $url;
    }
    
    protected function _getPaymentCancelUrl(Sabai_Context $context, array $formStorage)
    {
        return $this->Url('/' . $this->getAddon('Directory')->getSlug('dashboard'), array(), '', '&');
    }
}