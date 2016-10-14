<?php
class Sabai_Addon_Directory_Controller_ClaimListing extends Sabai_Addon_Form_MultiStepController
{
    protected $_claimStatus = 'pending';
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Only registered users can claim listings
        if ($this->getUser()->isAnonymous() && !$this->_isRegisterable()) {
            $context->setUnauthorizedError($this->Entity_Url($context->entity, '/' . $this->Entity_Addon($context->entity)->getSlug('claim')));
            return false;
        }
        
        $settings = parent::_doGetFormSettings($context, $formStorage);
        $settings['dashboard'] = array('#type' => 'hidden', '#value' => $context->getRequest()->asInt('dashboard'));
        $this->_cancelUrl = $this->_getCancelUrl($context);
        return $settings;
    }
    
    protected function _getCancelUrl(Sabai_Context $context)
    {
        return $context->getRequest()->asInt('dashboard')
            ? '/' . $this->getAddon('Directory')->getSlug('dashboard')
            : $this->Entity_Url($context->entity);
    }
    
    protected function _getSteps(Sabai_Context $context, array &$formStorage)
    {
        return $this->getUser()->isAnonymous() ? array('register', 'claim') : array('claim');
    }
    
    protected function _isRegisterable()
    {
        return $this->getAddon('Directory')->getConfig('display', 'register');
    }
    
    protected function _getFormForStepRegister(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons = array(
            'register' => array(
                '#value' => __('Register now', 'sabai-directory'),
                '#weight' => 1,
                '#btn_type' => 'primary',
            ),
        );
        return array(
            '#title' => __('Create an Account', 'sabai-directory'),
            '#collapsible' => false,
            'login' => array(
                '#type' => 'markup',
                '#markup' => '<p>' . sprintf(__('Already registered? Click <a href="%s" class="sabai-login popup-login">here</a> to login.', 'sabai-directory'), $this->LoginUrl($this->Url((string)$context->getRoute()))) . '</p>',
            ),
        ) + $this->getPlatform()->getRegisterForm();
    }
    
    protected function _submitFormForStepRegister(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        if ($form->getClickedButtonName() === 'register'
            && ($user_id = $this->getPlatform()->registerUser($form->values))
            && $this->getPlatform()->loginUser($user_id)
        ) {
            $context->setRedirect((string)$context->getRoute());
        }
    }
    
    protected function _getFormForStepClaim(Sabai_Context $context, array &$formStorage)
    {
        $header = $this->getPlatform()->getOption($this->Entity_Addon($context->entity)->getName() . '_claim_form_header');
        if (!strlen($header)) {
            $header = __('If the listing is for your organisation, please complete the details below. Once we have confirmed your identity, we will give you full control over your listing and its contents.', 'sabai-directory');
        }
        $headers = array();
        $headers[] = '<div class="sabai-alert sabai-alert-info">' . $header .'</div>';
        $form = array(
            '#disable_back_btn' => true,
            '#header' => $headers,
            'listing' => array(
                '#type' => 'item',
                '#title' => __('Listing', 'sabai-directory'),
                '#markup' => $this->Entity_Permalink($context->entity),
            ),
            'name' => array(
                '#type' => 'textfield',
                '#required' => true,
                '#title' => __('Contact name', 'sabai-directory'),
                '#default_value' => $this->getUser()->name,
            ),
            'email' => array(
                '#type' => 'email',
                '#required' => true,
                '#title' => __('E-mail', 'sabai-directory'),
                '#default_value' => $this->getUser()->email,
            ),
            'comment' => array(
                '#required' => $this->Entity_Addon($context->entity)->getConfig('claims', 'no_comment') ? false : true,
                '#type' => 'markdown_textarea',
                '#title' => __('Comment', 'sabai-directory'),
                '#description' => __('Please provide additional information that will allow us to verify your claim.', 'sabai-directory'),
                '#rows' => 5,
                '#hide_buttons' => true,
                '#hide_preview' => true,
            ),
        );
        $this->_submitButtons[] = array(
            '#value' => __('Submit Claim', 'sabai-directory'),
            '#btn_type' => 'primary',
        );
        
        return $form;
    }

    protected function _submitFormForStepClaim(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {        
        $claim = $this->_createClaim($form, $context->entity)->commit()->reload();
        $form->storage['claim_id'] = $claim->id;
        $form->storage['claim_status'] = $claim->status;
        $this->Action('directory_listing_claim_status_change', array($claim));
        if ($claim->status === 'approved') {
            $claims_config = $this->Entity_Addon($context->entity)->getConfig('claims');
            $this->Directory_ClaimListing($claim->Entity, $claim->User, $claims_config['duration']);
            if ($claims_config['process']['delete_auto_approved']) {
                $claim->markRemoved()->commit();
            }
        }
    }
    
    protected function _createClaim(Sabai_Addon_Form_Form $form, Sabai_Addon_Entity_Entity $entity)
    {
        $claim = $this->getModel(null, 'Directory')->create('Claim')->markNew();
        $claim->name = $form->values['name'];
        $claim->email = $form->values['email'];
        $claim->comment = $form->values['comment'];
        $claim->entity_id = $entity->getId();
        $claim->entity_bundle_name = $entity->getBundleName();
        $claim->User = $this->getUser();
        $claim->type = 'claim';
        $claim->status = $this->Entity_Addon($entity)->getConfig('claims', 'process', 'auto_approve') ? 'approved' : 'pending';
        return $claim;
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {       
        $context->addTemplate('form_results')
            ->setAttributes(array(
                'success' => array(
                    $formStorage['claim_status'] === 'approved'
                        ? __('Your claim has been submitted successfully and approved.', 'sabai-directory')
                        : __('Your claim has been submitted successfully. We will review your claim details and notify you if it is approved.', 'sabai-directory')
                )
            ));
    }  
}