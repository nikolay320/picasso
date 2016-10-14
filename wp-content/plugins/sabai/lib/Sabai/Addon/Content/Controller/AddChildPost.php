<?php
class Sabai_Addon_Content_Controller_AddChildPost extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_ajaxCancelType = 'none';
        $this->_ajaxSubmit = false;
        $this->_submitButtons['submit'] = array(
            '#value' => sprintf(__('Post %s', 'sabai'), $this->Entity_BundleLabel($context->child_bundle, true)),
            '#btn_type' => 'primary',
            '#attributes' => array('class' => 'sabai-content-btn-add-' . str_replace('_', '-', $context->child_bundle->type)),
        );

        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }

        $form = $this->Entity_Form($context->child_bundle, $values);
        // Remove parent content selection field
        unset($form['content_parent']);
        // Add parent content static field if not on modal window or Ajax request
        if ($context->getContainer() !== '#sabai-modal'
            && !$context->getRequest()->isAjax()
            && strpos($context->getContainer(), '#sabai-inline') !== 0
        ) {
            $form['content_parent'] = array(
                '#type' => 'item',
                '#title' => $this->Entity_BundleLabel($context->entity, true),
                '#markup' => $this->Entity_Permalink($context->entity),
                '#weight' => -1,
            );
            $this->_cancelUrl = $this->Entity_Url($context->entity);
        }
        
        $context->clearTabs();
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $values = array();
        // Set the current entity as the parent
        $values['content_parent'] = array($context->entity->getId());
        // Mark post as pending if no permission to post without approval
        $values['content_post_status'] = $this->_getContentPostStatus($context);
        $entity = $form->settings['#entity'] = $this->Entity_Save($context->child_bundle, $values + $form->values);
        if ($entity->isPublished()) {
            if (!isset($context->child_bundle->info['public']) || false !== $context->child_bundle->info['public']) {            
                $context->setSuccess($this->Entity_Url($entity));
            } else {
                $context->addTemplate('form_results')->setAttributes(array(
                    'success' => __('Your post has been submitted successfully.', 'sabai'),
                    'info' => sprintf(__('Back to %s', 'sabai'), $this->Entity_Link($context->entity)),
                ));
            }
        } else {
            $context->addTemplate('form_results')->setAttributes(array(
                'success' => __('Thanks for your submission, we will review it and get back with you.', 'sabai'),
                'info' => sprintf(__('Back to %s', 'sabai'), $this->Entity_Link($context->entity)),
            ));
        }      
        $this->Action('content_child_entity_created', array($entity, $context->entity));

        // Set cookie to track guest user
        if ($this->getUser()->isAnonymous()) {
            $this->Entity_SetGuestAuthorCookie($entity);
        }
        
        return $entity;
    }
    
    protected function _getContentPostStatus(Sabai_Context $context)
    {
        return $this->HasPermission($context->child_bundle->name . '_add2')
            ? Sabai_Addon_Content::POST_STATUS_PUBLISHED
            : Sabai_Addon_Content::POST_STATUS_PENDING;
    }
}
