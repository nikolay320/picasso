<?php
class Sabai_Addon_Content_Controller_EditPost extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_ajaxCancelType = 'none';
        $this->_submitButtons['submit'] = array(
            '#value' => __('Save Changes', 'sabai'),
            '#btn_type' => 'primary',
            '#attributes' => array('class' => 'sabai-content-btn-edit-' . str_replace('_', '-', $context->bundle->type)),
        );

        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }

        $form = $this->Entity_Form($context->entity, $values);
        
        $context->clearTabs();
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {        
        $entity = $this->Entity_Save($context->entity, $form->values);
        $context->setSuccess($this->Entity_Url($entity));
        
        // Set cookie to track guest user
        if ($this->getUser()->isAnonymous()) {
            $this->Entity_SetGuestAuthorCookie($entity);
        }
        
        return $entity;
    }
}
