<?php
require_once SABAI_PACKAGE_PAIDLISTINGS_PATH . '/lib/PaidListings/Controller/PricingTable.php';

class Sabai_Addon_PaidDirectoryListings_Controller_PricingTable extends Sabai_Addon_PaidListings_Controller_PricingTable
{    
    protected function _doExecute(Sabai_Context $context)
    {
        if (isset($context->columns)) {
            $this->_columns = $context->columns;
        }
        if (isset($context->link_others)) {
            $this->_linkOtherPaymentTypes = (bool)$context->link_others;
        }

        parent::_doExecute($context);
    }
    
    protected function _getPaymentTypes(Sabai_Context $context)
    {
        return isset($context->payment_type) ? explode(',', $context->payment_type) : parent::_getPaymentTypes($context);
    }
    
    protected function _getButtonLabel(Sabai_Context $context)
    {
        return isset($context->button_label) ? $context->button_label : parent::_getButtonLabel($context);
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        $addon = isset($context->addon) ? $context->addon : 'Directory';
        if (!$this->isAddonLoaded($addon)) {
            throw new Sabai_RuntimeException(sprintf('Addon %s is not active.', $addon));
        }
        return $this->getAddon($addon)->getListingBundleName();
    }
    
    protected function _getPlanOrderUrl(Sabai_Context $context, $bundleName, Sabai_Addon_PaidListings_Model_Plan $plan, $paymentType = null)
    {
        $args = array('bundle' => $bundleName, 'plan' => $plan->id);
        if (isset($paymentType)) {
            $args['type'] = $paymentType;
        }
        return $this->Url('/' . $this->getAddon('Directory')->getSlug('add-listing'), $args);
    }
}