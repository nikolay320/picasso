<?php
class Sabai_Addon_Content_Controller_RedirectToParentPost extends Sabai_Addon_Content_Controller_ViewPost
{
    protected function _doExecute(Sabai_Context $context)
    {
        $parent_post = $this->Content_ParentPost($context->entity, false);
        if (!$parent_post) {
            $context->setError();
            return;
        }
        if (!$context->getRequest()->isAjax()) {
            // Redirect to parent entity page
            $context->setRedirect($this->_getRedirectUrl($context, $parent_post));
        }
        
        $context->parent_entity = $parent_post;
        // Show content if Ajax request
        parent::_doExecute($context);
    }
    
    protected function _getRedirectUrl(Sabai_Context $context, Sabai_Addon_Entity_Entity $parentEntity)
    {
        return $this->Entity_Url($parentEntity);
    }
}
