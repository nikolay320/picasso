<?php
class Sabai_Addon_Content_Controller_ViewPost extends Sabai_Addon_Entity_Controller_ViewEntity
{    
    protected function _doExecute(Sabai_Context $context)
    {
        if (isset($context->bundle->info['public']) && $context->bundle->info['public'] === false) {
            $context->setForbiddenError();
            return;
        }
        
        parent::_doExecute($context);
        
        // Increment view count
        $this->Content_IncrementPostView($context->entity, true);
    }
    
    protected function _getEntity(Sabai_Context $context)
    {
        return $context->entity;
    }
}