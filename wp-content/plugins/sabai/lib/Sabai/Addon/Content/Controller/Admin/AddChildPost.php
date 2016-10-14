<?php
class Sabai_Addon_Content_Controller_Admin_AddChildPost extends Sabai_Addon_Content_Controller_Admin_AddPost
{
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->child_bundle;
    }
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        if (isset($form['content_parent'])
            && ($content_parent = $context->getRequest()->asInt('content_parent'))
        ) {
            $form['content_parent']['#default_value'] = array($content_parent);
        }
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $entity = parent::submitForm($form, $context);
        $this->Action('content_child_entity_created', array($entity, $this->Content_ParentPost($entity)));
        return $entity;
    }
}