<?php
require_once SABAI_PACKAGE_PAIDLISTINGS_PATH . '/lib/PaidListings/Controller/ViewOrders.php';
  
class Sabai_Addon_PaidDirectoryListings_Controller_Orders extends Sabai_Addon_PaidListings_Controller_ViewOrders
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        unset($form['orders']['#header']['user']);
        return $form;
    }

    protected function _getCriteria(Sabai_Context $context)
    {
        return ($plans = parent::_getCriteria($context)) ? $plans->userId_is($this->getUser()->id) : null;
    }
    
    protected function _isValidOrder(Sabai_Context $context, Sabai_Addon_PaidListings_Model_Order $order)
    {
        return $order->user_id === $this->getUser()->id;
    }
    
    protected function _getEntityBundleName(Sabai_Context $context)
    {
        $ret = array();
        foreach ($this->getModel('Bundle', 'Entity')->type_is('directory_listing')->fetch() as $bundle) {
            if (!$this->isAddonLoaded($bundle->addon)) continue;
            
            $ret[] = $bundle->name;
        }
        return $ret;
    }
}
