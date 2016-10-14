<?php
class Sabai_Addon_Directory_Controller_AddListing extends Sabai_Addon_Form_MultiStepController
{
    protected $_submittableDirectories, $_submittableDirectoriesAsGuest, $_maxNumValues = array(), $_forcesSelectDirectory = false;
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Fetch submittable directory options
        if (!$this->_getSubmittableDiretctories($context)) {
            // The user is not allowed to submit listings to any directory
            // Let the user login if guest
            if ($this->getUser()->isAnonymous()) {
                $context->setUnauthorizedError('/' . $this->getAddon('Directory')->getSlug('add-listing'));
            }
            return false;
        } else {
            if ($this->getUser()->isAnonymous()) {
                // Redirecto to login page if no guest submittable directories and registration not allowed 
                if (empty($this->_submittableDirectoriesAsGuest) && !$this->_isRegisterable()) {
                    $context->setUnauthorizedError('/' . $this->getAddon('Directory')->getSlug('add-listing'));
                    return false;
                }
            }
        }
        $context->clearTabs();
        $settings = parent::_doGetFormSettings($context, $formStorage);
        $this->_ajaxCancelType = 'none';
        $this->_cancelUrl = $this->_getCancelUrl($context);
        
        return $settings;
    }
    
    protected function _getCancelUrl(Sabai_Context $context)
    {
        return $this->getUser()->isAnonymous()
            ?  '/' . $this->getAddon('Directory')->getSlug('directory')
            : '/' . $this->getAddon('Directory')->getSlug('dashboard');
    }
    
    protected function _isRegisterable()
    {
        return $this->getAddon('Directory')->getConfig('display', 'register');
    }
    
    protected function _getSteps(Sabai_Context $context, array &$formStorage)
    {
        if ($this->getUser()->isAnonymous()) {
            if (!$this->_forcesSelectDirectory
                && ($bundle = $this->_hideSelectDirectory($formStorage))
            ) {
                $formStorage['bundle'] = $bundle;
                return $this->_isRegisterable() ? array('register', 'add') : array('add');
            }
            return $this->_isRegisterable() ? array('register', 'select_directory', 'add') : array('select_directory', 'add');
        }
        
        if (!$this->_forcesSelectDirectory
            && ($bundle = $this->_hideSelectDirectory($formStorage))
        ) {
            $formStorage['bundle'] = $bundle;
            return array('add');
        }
        return array('select_directory', 'add');
    }
    
    protected function _hideSelectDirectory(array &$formStorage)
    {
        if (!isset($formStorage['hide_select_directory'])) {
            $directories = $this->getUser()->isAnonymous() ? $this->_submittableDirectoriesAsGuest : $this->_submittableDirectories;
            $formStorage['hide_select_directory'] = isset($_GET['bundle']) && isset($directories[$_GET['bundle']])
                ? $_GET['bundle']
                : (count($directories) === 1 ? current(array_keys($directories)) : false);
        }
        return $formStorage['hide_select_directory'];
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
        if (!empty($this->_submittableDirectoriesAsGuest)
            && $this->getAddon('Directory')->getConfig('display', 'register_skip')
        ) {
            $this->_submitButtons['no_register'] = array(
                '#value' => __('Proceed without registration', 'sabai-directory'),
                '#weight' => 2,
                '#btn_type' => 'primary',
                '#skip_validate' => true,
            );
        }
        return array(
            '#title' => __('Create an Account', 'sabai-directory'),
            '#collapsible' => false,
            'login' => array(
                '#type' => 'markup',
                '#markup' => '<p>' . sprintf(__('Already registered? Click <a href="%s" class="sabai-login popup-login">here</a> to login.', 'sabai-directory'), $this->LoginUrl($this->Url((string)$context->getRoute(), array('bundle' => @$formStorage['bundle'])))) . '</p>',
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
    
    protected function _getSubmittableDiretctories(Sabai_Context $context)
    {
        if (!isset($this->_submittableDirectories)) {
            // Fetch directory options
            $options = $this->Directory_DirectoryList();
            // Directories where guest users are allowed to submit
            $guest_options = array();
            
            if (($is_guest = $this->getUser()->isAnonymous())
                && ($is_registerable = $this->getAddon('Directory')->getConfig('display', 'register'))
            ) {
                $default_user_role = $this->getPlatform()->getDefaultUserRole();
            }
            
            foreach (array_keys($options) as $directory_listing_bundle) {
                $perm = $directory_listing_bundle . '_add';
                if (!$this->HasPermission($perm)) {
                    if (!$is_guest
                        || !$is_registerable
                        || !$this->HasPermission($perm, $default_user_role) // Is the user role assigned during registration allowed to submit?
                    ) {
                        unset($options[$directory_listing_bundle]);
                    } 
                } else {
                    if ($is_guest) {
                        $guest_options[$directory_listing_bundle] = $options[$directory_listing_bundle];
                    }
                }
            }
            $this->_submittableDirectories = $this->Filter('directory_submit_options', $options);
            $this->_submittableDirectoriesAsGuest = $guest_options;
        }
        return $this->_submittableDirectories;
    }
    
    protected function _getFormForStepSelectDirectory(Sabai_Context $context, array &$formStorage)
    {
        $options = $this->getUser()->isAnonymous() ? $this->_submittableDirectoriesAsGuest : $this->_submittableDirectories;
        return array(
            'bundle' => array(
                '#title' => __('Directory', 'sabai-directory'),
                '#description' => __('Select the directory where you want to submit the new listing.', 'sabai-directory'),
                '#type' => 'radios',
                '#options' => $options,
                '#required' => true,
                '#default_value_auto' => true,
            ),
        );
    }
    
    protected function _submitFormForStepSelectDirectory(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        if (isset($form->settings['bundle'])) {
            $form->storage['bundle'] = @$form->values['bundle'];
        }
    }
        
    protected function _getFormForStepAdd(Sabai_Context $context, array &$formStorage)
    {
        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            if ($this->_isBack($context)) {
                if (isset($formStorage['values']['add'])) {
                    $values = $formStorage['values']['add'];
                }
            } else {
                $values = $context->getRequest()->getParams();
            }
        }

        $this->_submitButtons[] = array(
            '#value' => __('Submit Listing', 'sabai-directory'),
            '#btn_type' => 'primary',
        );

        $form = $this->Entity_Form($formStorage['bundle'], $values);
        
        if ($this->getUser()->isAnonymous()) {
            $unclaimed_listing_config = $this->Entity_Addon($formStorage['bundle'])->getConfig('claims', 'unclaimed');      
            // Limit location/category numbers?    
            foreach (array('directory_location', 'directory_category') as $field_name) {      
                if (isset($form[$field_name][0])) {
                    if (empty($unclaimed_listing_config[$field_name]['limit'])) continue;
                    
                    if (!$limit_num = @$unclaimed_listing_config[$field_name]['num']) {
                        unset($form[$field_name]);
                        continue;
                    }

                    if (isset($form[$field_name]['add'])) {
                        unset($form[$field_name]['add']);
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
            if (isset($unclaimed_listing_config['fields']) && is_array($unclaimed_listing_config['fields'])) {
                $form = $this->Directory_FilterFormFields($form, $unclaimed_listing_config['fields']);
            }
            // Limit photo numbers?
            if (isset($form['directory_photos'])) {
                if (!empty($unclaimed_listing_config['directory_photos']['limit'])) {
                    if (empty($unclaimed_listing_config['directory_photos']['num'])) {
                        unset($form['directory_photos']);
                    } else {
                        if ($unclaimed_listing_config['directory_photos']['num'] < $form['directory_photos']['#max_num_files']) {
                            $form['directory_photos']['#max_num_files'] = $unclaimed_listing_config['directory_photos']['num'];
                        }
                    }
                }
            }
            $options = $this->_getSubmittableDiretctories($context);
            if (count($options) === 1) {
                $form['#back_to'] = 'register';
            }
        } else {
            $options = $this->_getSubmittableDiretctories($context);
            if (count($options) === 1) {
                $form['#disable_back_btn'] = true;
            }
        }
        
        return $form;
    }

    protected function _submitFormForStepAdd(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        if (empty($form->storage['listing_id'])
            || (!$listing = $this->_getListing($form->storage))
        ) {
            $bundle_name = $form->storage['bundle'];
            $status = $this->_getPostStatus($context, $form, $bundle_name);
            // Create listing and save entity id into session for later use
            $listing = $this->Entity_Save($bundle_name, array('content_post_status' => $status) + $form->values, array('entity_field_max_num_values' => $this->_maxNumValues));
            $form->storage['listing_id'] = $listing->getId();
        } else {
            $listing = $this->Entity_Save($listing, $form->values, array('entity_field_max_num_values' => $this->_maxNumValues));
        }
        $form->settings['#entity'] = $listing;
        
        if ($this->getUser()->isAnonymous()) return;

        $claim = $this->_createClaim($form, $listing)->commit()->reload();
        $form->storage['claim_id'] = $claim->id;
        $this->Action('directory_listing_claim_status_change', array($claim));
        if ($claim->status === 'approved') {
            $claims_config = $this->Entity_Addon($listing)->getConfig('claims');
            $this->Directory_ClaimListing($claim->Entity, $claim->User, $claims_config['duration']);
            if ($claims_config['process']['delete_auto_approved']) {
                $claim->markRemoved()->commit();
            }
        }
    }
    
    protected function _createClaim(Sabai_Addon_Form_Form $form, Sabai_Addon_Entity_Entity $entity)
    {
        $claim = $this->getModel(null, 'Directory')->create('Claim')->markNew();
        $claim->name = $this->getUser()->name;
        $claim->email = $this->getUser()->email;
        $claim->comment = '';
        $claim->entity_id = $entity->getId();
        $claim->entity_bundle_name = $entity->getBundleName();
        $claim->User = $this->getUser();
        $claim->type = 'new';
        $claim->status = $this->Entity_Addon($entity)->getConfig('claims', 'process', 'auto_approve_new') ? 'approved' : 'pending';
        return $claim;
    }
    
    protected function _complete(Sabai_Context $context, array $formStorage)
    {
        $entity = $this->_getListing($formStorage);
        // Set cookie to track guest user
        if ($this->getUser()->isAnonymous()) {
            $this->Entity_SetGuestAuthorCookie($entity);
        }
        // Display messages
        $context->addTemplate('form_results')->setAttributes(array('success' => array(
            $entity->isPublished()
                ? sprintf(__('Your listing has been submitted successfully and published. You can view the listing <a href="%s" target="_blank">here</a>.', 'sabai-directory'), $this->Entity_Url($entity))
                : __('Your listing has been submitted successfully. We will review your submission and post it on this site if it is approved.', 'sabai-directory')
        )));
    }
    
    protected function _getListing(array $formStorage)
    {
        if (empty($formStorage['listing_id'])
            || (!$listing = $this->Entity_Entity('content', $formStorage['listing_id'], false))
        ) {
            throw new Sabai_RuntimeException('Invalid listing');
        }
        return $listing;
    }
    
    protected function _getPostStatus(Sabai_Context $context, Sabai_Addon_Form_Form $form, $bundleName)
    {
        return $this->HasPermission($bundleName . '_add2') // can post without approval?
            ? Sabai_Addon_Content::POST_STATUS_PUBLISHED
            : Sabai_Addon_Content::POST_STATUS_PENDING;
    }
}
