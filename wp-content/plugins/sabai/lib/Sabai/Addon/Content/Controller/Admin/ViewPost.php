<?php
class Sabai_Addon_Content_Controller_Admin_ViewPost extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {
        $url = $context->entity->isPublished() ? $this->Entity_Url($context->entity) : $this->Content_PreviewUrl($context->entity);
        $context->setRedirect($url);
    }
}