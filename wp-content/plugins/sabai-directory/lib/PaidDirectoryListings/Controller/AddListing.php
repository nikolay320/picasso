<?php
require_once dirname(dirname(dirname(__FILE__))) . '/Directory/Controller/AddListing.php';

class Sabai_Addon_PaidDirectoryListings_Controller_AddListing extends Sabai_Addon_Directory_Controller_AddListing
{    
    protected function _getSteps(Sabai_Context $context, array &$formStorage)
    {
        if (!$this->getUser()->isAnonymous()) {
            $this->_forcesSelectDirectory = true;
            if (($bundle = $this->_hideSelectDirectory($formStorage))
                && ($plan = $this->_hideSelectPlan($bundle, $formStorage))
            ) {
                $formStorage['values']['select_directory'][$bundle . '_plan'] = $plan['plan'];
                $formStorage['values']['select_directory'][$bundle . '_plan_type'] = $plan['type'];
                $this->_forcesSelectDirectory = false;
            }
        }
        $steps = parent::_getSteps($context, $formStorage);
        return $this->getUser()->isAnonymous() ? $steps : array_merge($steps, array('checkout'));
    }
    
    protected function _hideSelectPlan($bundle, array &$formStorage)
    {
        if (!isset($formStorage['hide_select_plan'])) {
            $formStorage['hide_select_plan'] = false;
            if (!empty($_GET['plan'])
                && ($plan = $this->PaidListings_ActivePlans($bundle, 'base', $_GET['plan']))
            ) {
                $formStorage['hide_select_plan']= array(
                    'plan' => $_GET['plan'],
                    'type' => null,
                );
                if (isset($_GET['type'])) {
                    try {
                        $this->PaidListings_ValidatePayment($plan, $_GET['type']);
                        $formStorage['hide_select_plan']['type'] = $_GET['type'];
                    } catch (Sabai_IException $e) {
                        $this->LogError($e);
                    }
                }
            }
        }
        return $formStorage['hide_select_plan'];
    }
    
    protected function _getFormForStepSelectDirectory(Sabai_Context $context, array &$formStorage)
    {
        if (false === $form = parent::_getFormForStepSelectDirectory($context, $formStorage)) {
            return false;
        }
        if ($bundle = $this->_hideSelectDirectory($formStorage)) {
            if (!$plans = $this->PaidListings_ActivePlans($bundle, 'base')) {
                $context->setError(__('There are no payment plans available in this directory.', 'sabai-directory'), $this->_getCancelUrl($context));
                return false;
            }
            $form['bundle']['#type'] = 'hidden';
            $form['bundle']['#default_value'] = $bundle;
            $formStorage['bundle'] = $bundle;
        }
        // Add plan selection form if not a guest user
        if (!$this->getUser()->isAnonymous()) {
            $has_plan = false;
            foreach (array_keys($form['bundle']['#options']) as $bundle_name) {
                if ($plans = $this->PaidListings_ActivePlans($bundle_name, 'base')) {
                    $form[$bundle_name . '_plan'] = array(
                        '#type' => 'paidlistings_select_plan',
                        '#plans' => $plans,
                        '#required' => array(array($this, 'isPlanRequired'), array($bundle_name)),
                        '#states' => array(
                            'visible' => array(
                                'input[name="bundle"]' => array('value' => $bundle_name),
                            ),
                        ),
                    );
                    $has_plan = true;
                } else {
                    // disable this add-on since there are no plans
                    unset($form['bundle']['#options'][$bundle_name]);
                }
            }
            
            if (!$has_plan) {
                $context->setError(__('There are no payment plans available in this directory.', 'sabai-directory'), $this->_getCancelUrl($context));
                return false;
            }
            
            if (!$bundle) {
                // hide directory selection if only 1 directory
                if (count($form['bundle']['#options']) === 1) {
                    $form['bundle']['#type'] = 'hidden';
                    $form['bundle']['#default_value'] = $formStorage['bundle'] = current(array_keys($form['bundle']['#options']));
                }
            }
        }
        
        return $form;
    }
    
    public function isPlanRequired($form, $bundleName)
    {
        return @$form->values['bundle'] === $bundleName;
    }
    
    protected function _getSelectedPlan(array $formStorage)
    {
        if ((!$plan_id = $formStorage['values']['select_directory'][$formStorage['bundle'] . '_plan'])
            || (!$plan = $this->PaidListings_ActivePlans($formStorage['bundle'], 'base', $plan_id))
        ) {
            return false;
        }
        return $plan;
    }

    protected function _getPaymentOrder(array $formStorage)
    {
        if (empty($formStorage['order_id'])
            || (!$order = $this->getModel('Order', 'PaidListings')->fetchById($formStorage['order_id'], false, false))
        ) {
            throw new Sabai_RuntimeException('Invalid payment order');
        }
        return $order;
    }
    
    protected function _getFormForStepAdd(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_getFormForStepAdd($context, $formStorage);
        $this->_submitButtons = array();
        
        if (!$this->getUser()->isAnonymous()
            && ($plan = $this->_getSelectedPlan($formStorage))
        ) {
            $form['#disable_back_btn'] = false;
            // Limit location/category numbers?    
            foreach (array('directory_location', 'directory_category') as $field_name) {
                if (isset($form[$field_name][0])) {
                    if (!@$plan->features['paiddirectorylistings_claim'][$field_name]['limit']) continue;
                    
                    $limit_num = @$plan->features['paiddirectorylistings_claim'][$field_name]['num'];
                    if (empty($limit_num)) {
                        unset($form[$field_name]);
                        continue;
                    }
                    if (isset($form[$field_name]['_add'])) {
                        unset($form[$field_name]['_add']);
                    } else {
                        $current_num = 0;
                        foreach (array_keys($form[$field_name]) as $key) {
                            if (is_numeric($key)) {
                                ++$current_num;
                                if ($key + 1 > $limit_num) {
                                    // over limit num
                                    unset($form[$field_name][$key]);
                                }
                            }
                        }
                        if ($current_num < $limit_num) {
                            $limit_num = $current_num;
                        }
                    }
                    for ($i = 1; $i < $limit_num; $i++) {
                        if (!isset($form[$field_name][$i])) {
                            $form[$field_name][$i] = $form[$field_name][0];
                            $form[$field_name][$i]['#default_value'] = null;
                            $form[$field_name][$i]['#required'] = false;
                        }
                    }
                    $this->_maxNumValues[$field_name] = $limit_num;
                }
            }
            // Limit fields?
            if (null !== $limit_fields = @$plan->features['paiddirectorylistings_claim']['fields']) {
                $form = $this->Directory_FilterFormFields($form, $limit_fields);
            }
            // Limit photo numbers?
            if (isset($form['directory_photos'])) {
                if (@$plan->features['paiddirectorylistings_claim']['directory_photos']['limit']) {
                    $limit_num = @$plan->features['paiddirectorylistings_claim']['directory_photos']['num'];
                    if (empty($limit_num)) {
                        unset($form['directory_photos']);
                    } else {
                        $form['directory_photos']['#max_num_files'] = $limit_num;
                    }
                }
            }

            $context->setTitle(sprintf(__('%s (plan: %s)'), __('Add Listing', 'sabai-directory'), $plan->name));
        }
        
        return $form;
    }
    
    protected function _getFormForStepCheckout(Sabai_Context $context, array &$formStorage)
    {
        if ($this->getUser()->isAnonymous()
            || (!$plan = $this->_getSelectedPlan($formStorage))
        ) {
            return $this->_skipStepAndGetForm($context, $formStorage);
        }
        
        return $this->PaidListings_PaymentForm($plan, $formStorage, @$formStorage['values']['select_directory'][$formStorage['bundle'] . '_plan_type']);
    }
    
    protected function _submitFormForStepCheckout(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        // Create order
        $entity = $this->_getListing($form->storage);
        $plan = $this->_getSelectedPlan($form->storage);
        $order_data = array(
            'paiddirectorylistings_claim' => array(
                'claim_id' => $form->storage['claim_id'],
                'claim_new' => 1
            ),
        );
        $payment_type = $form->values['payment_type'];
        $order = $this->PaidListings_CreateOrder($entity, $plan, $payment_type, $order_data);
        // Checkout gateway
        if ($order->price) {
            $return_url = $this->Url($context->getRoute(), array(Sabai_Addon_Form::FORM_BUILD_ID_NAME => $context->getRequest()->asStr(Sabai_Addon_Form::FORM_BUILD_ID_NAME)), '', '&');
            $cancel_url = $this->Url($context->getRoute(), array(), '', '&');
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
        $this->Entity_Save($entity, array('paidlistings_plan' => $entity_data));
        // Store order ID for later use
        $form->storage['order_id'] = $order->id;
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {        
        if ($this->getUser()->isAnonymous()
            || (!$plan = $this->_getSelectedPlan($formStorage))
        ) {
            parent::_complete($context, $formStorage);
            return;
        }
        
        $order = $this->_getPaymentOrder($formStorage);
        $this->Action('paidlistings_order_received', array($order));
        $this->Action('paidlistings_order_status_change', array($order));
        
        parent::_complete($context, $formStorage);
        
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
    
    protected function _getPostStatus(Sabai_Context $context, Sabai_Addon_Form_Form $form, $bundleName)
    {
        if ($this->getUser()->isAnonymous()
            || (!$plan = $this->_getSelectedPlan($form->storage))
        ) {
            return parent::_getPostStatus($context, $form, $bundleName);
        }
        // Always mark the listing pending since the listing will be published upon
        // the PaidListingsOrderStatusChange event if the user has the permission to add listings without approval 
        return Sabai_Addon_Content::POST_STATUS_PENDING;
    }
    
    protected function _createClaim(Sabai_Addon_Form_Form $form, Sabai_Addon_Entity_Entity $entity)
    {
        $claim = parent::_createClaim($form, $entity);
        if ($this->PaidListings_ActivePlans($form->storage['bundle'], 'base')) {
            $claim->status = 'pending_payment';
        }
        return $claim;
    }
}
