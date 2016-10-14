<?php
class Sabai_Addon_Content_Controller_AddPost extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $context->bundle->getPath();
        $this->_ajaxCancelType = 'none';
        $this->_ajaxSubmit = false;
        $this->_submitButtons['submit'] = array(
            '#value' => sprintf(__('Post %s', 'sabai'), $this->Entity_BundleLabel($context->bundle, true)),
            '#btn_type' => 'primary',
            '#attributes' => array('class' => 'sabai-content-btn-add-' . str_replace('_', '-', $context->bundle->type)),
        );

        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }

        $form = $this->Entity_Form($context->bundle, $values);
        
        $context->clearTabs();
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $entity = $form->settings['#entity'] = $this->Entity_Save($context->bundle, array('content_post_status' => $this->_getContentPostStatus($context)) + $form->values);
        if ($entity->isPublished()) {
            $context->setSuccess($this->Entity_Url($entity));
        } else {
            $context->addTemplate('form_results')->setAttributes(array(
                'success' => __('Thanks for your submission, we will review it and get back with you.', 'sabai'),
            ));
        }
        
        // Set cookie to track guest user
        if ($this->getUser()->isAnonymous()) {
            $this->Entity_SetGuestAuthorCookie($entity);
        }
        
        return $entity;
    }
    
    protected function _getContentPostStatus(Sabai_Context $context)
    {
        return $this->HasPermission($context->bundle->name . '_add2') // can post without approval?
            ? Sabai_Addon_Content::POST_STATUS_PUBLISHED
            : Sabai_Addon_Content::POST_STATUS_PENDING;
    }
}
