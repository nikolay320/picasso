<?php
class Sabai_Addon_Questions_Controller_AddAnswer extends Sabai_Addon_Content_Controller_AddChildPost
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if ($context->entity->getSingleFieldValue('questions_closed')) {
            $context->setErrorUrl($this->Entity_Url($context->entity));
            return false;
        }
        
        return parent::_doGetFormSettings($context, $formStorage);
    }
}
