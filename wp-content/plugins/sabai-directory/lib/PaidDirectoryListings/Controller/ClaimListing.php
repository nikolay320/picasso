<?php
require_once dirname(dirname(dirname(__FILE__))) . '/Directory/Controller/ClaimListing.php';

class Sabai_Addon_PaidDirectoryListings_Controller_ClaimListing extends Sabai_Addon_Directory_Controller_ClaimListing
{    
    protected function _getSteps(Sabai_Context $context, array &$formStorage)
    {
        return array_merge(parent::_getSteps($context, $formStorage), array('checkout'));
    }
    
    protected function _getFormForStepClaim(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_getFormForStepClaim($context, $formStorage);
        if (!$plans = $this->PaidListings_ActivePlans($context->entity->getBundleName(), 'base')) {
            $context->setError(__('There are no payment plans available in this directory.', 'sabai-directory'), $this->_getCancelUrl($context));
            return false;
        }
        
        $form += array(
            'plan' => array(
                '#type' => 'paidlistings_select_plan',
                '#required' => true,
                '#plans' => $plans,
            ),
        );
        $this->_ajaxSubmit = false;
        $this->_submitButtons = array();
        
        return $form;
    }
    
    protected function _getFormForStepCheckout(Sabai_Context $context, array &$formStorage)
    {
        if (!$plan = $this->_getSelectedPlan($formStorage)) {
            return $this->_skipStepAndGetForm($context, $formStorage);
        }
        
        return $this->PaidListings_PaymentForm($plan, $formStorage);
    }
    
    protected function _submitFormForStepCheckout(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        // Create order
        $plan = $this->_getSelectedPlan($form->storage);
        $order_data = array(
            'paiddirectorylistings_claim' => array(
                'claim_id' => $form->storage['claim_id'],
            ),
        );
        $order = $this->PaidListings_CreateOrder($context->entity, $plan, $form->values['payment_type'], $order_data);
        // Checkout gateway
        if ($order->price) {
            $route = $context->getRoute(); 
            $return_url = $this->Url($route, array(Sabai_Addon_Form::FORM_BUILD_ID_NAME => $context->getRequest()->asStr(Sabai_Addon_Form::FORM_BUILD_ID_NAME)), '', '&');
            $cancel_url = $this->Url($route, array(), '', '&');
            $gateway = $form->values['method'];
            $this->getAddon($gateway)->paidListingsGatewayCheckout($form, $order, $return_url, $cancel_url);
            if ($form->hasError()) {
                $order->markRemoved()->getModel()->commit();
                return false;
            }
        } else {
            $order->markPaymentPaid(true);
        }
        // Update entity
        $entity_data = $order->getEntityData();
        $entity_data['plan_id'] = $plan->id;
        $this->Entity_Save($context->entity, array('paidlistings_plan' => $entity_data));
        // Store order ID for later use
        $form->storage['order_id'] = $order->id;
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {
        parent::_complete($context, $formStorage);
        
        if (!$this->PaidListings_ActivePlans($context->entity->getBundleName(), 'base')) return;
        
        $order = $this->_getPaymentOrder($formStorage);
        $this->Action('paidlistings_order_received', array($order));
        $this->Action('paidlistings_order_status_change', array($order));
        
        $order->reload();
        switch ($order->status) {
            case Sabai_Addon_PaidListings::ORDER_STATUS_COMPLETE:
                $key = 'success';
                break;
            case Sabai_Addon_PaidListings::ORDER_STATUS_PAID:
            case Sabai_Addon_PaidListings::ORDER_STATUS_PROCESSING:
            case Sabai_Addon_PaidListings::ORDER_STATUS_PENDING:
                $key = 'info';
                break;
            default:
                $key = 'error';
                break;
        }
        if (!isset($context->$key)) {
            $context->$key = array();
        }
        $context->$key = array_merge($context->$key, array($order->getOrderStatusUserMessage()));

        // Add notice message by gateway
        if ($order->price
            && $order->gateway
            && $this->isAddonLoaded($order->gateway)
            && ($notice = $this->getAddon($order->gateway)->paidListingsGatewayGetPostCheckoutNotice($order))
        ) {
            if (!isset($context->info)) $context->info = array();
            $context->info[] = $notice;
        }
        
        $context->setTitle(__('Thank you for your order!', 'sabai-directory'));
    }
    
    protected function _getSelectedPlan(array $formStorage)
    {
        if ((!$plan_id = $formStorage['values']['claim']['plan'])
            || (!$plan = $this->getModel('Plan', 'PaidListings')->fetchById($plan_id))
        ) {
            throw new Sabai_RuntimeException('Invalid plan');
        }
        return $plan;
    }
    
    protected function _getPaymentOrder(array $formStorage)
    {
        if (empty($formStorage['order_id'])
            || (!$order = $this->getModel('Order', 'PaidListings')->fetchById($formStorage['order_id']))
        ) {
            throw new Sabai_RuntimeException('Invalid payment order');
        }
        return $order->reload();
    }
    
    protected function _createClaim(Sabai_Addon_Form_Form $form, Sabai_Addon_Entity_Entity $entity)
    {
        $claim = parent::_createClaim($form, $entity);
        $claim->status = 'pending_payment';
        return $claim;
    }
}
