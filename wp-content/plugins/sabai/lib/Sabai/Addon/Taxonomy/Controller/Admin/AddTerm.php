<?php
class Sabai_Addon_Taxonomy_Controller_Admin_AddTerm extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $context->taxonomy_bundle->getAdminPath();
        $this->_submitButtons[] = array('#value' => sprintf(__('Add %s', 'sabai'), $this->Entity_BundleLabel($context->taxonomy_bundle, true)));
        
        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings
        // but the entity form needs to check values to see if any form fields have been added dynamically (via JS) by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }
        
        $context->clearTabs();

        return $this->Entity_Form($context->taxonomy_bundle, $values, true);
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $entity = $this->Entity_Save($context->taxonomy_bundle->name, $form->values);
        $this->getPlatform()->deleteCache('taxonomy_terms_' . $context->taxonomy_bundle->name); // clear taxonomy terms cache
        $context->setSuccess($context->taxonomy_bundle->getAdminPath() . '/' . $entity->getId())
             ->addFlash(sprintf(__('%s created successfully.', 'sabai'), $this->Entity_BundleLabel($context->taxonomy_bundle, true)));
    }
}