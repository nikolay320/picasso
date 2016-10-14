<?php
class Sabai_Addon_Directory_Controller_TrashListing extends Sabai_Addon_Content_Controller_TrashPost
{
    protected $_defaultTrashType = Sabai_Addon_Content::TRASH_TYPE_OTHER, $_defaultOnly = true;
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $form = parent::_doGetFormSettings($context, $formStorage);        
        if ($context->getRequest()->asBool('dashboard')) {
            $this->_cancelUrl = '/' . $this->getAddon('Directory')->getSlug('dashboard');
            $form['dashboard'] = array('#type' => 'hidden', '#value' => 1);
        }

        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        parent::submitForm($form, $context);
        $context->setSuccessUrl($this->_cancelUrl);
    }
}
