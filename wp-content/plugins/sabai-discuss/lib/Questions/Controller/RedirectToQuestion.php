<?php
class Sabai_Addon_Questions_Controller_RedirectToQuestion extends Sabai_Addon_Content_Controller_RedirectToParentPost
{    
    protected function _getRedirectUrl(Sabai_Context $context, Sabai_Addon_Entity_Entity $parentEntity)
    {
        // Add fragment part
        return $this->Entity_Url($parentEntity, '', array(), 'sabai-entity-content-' . $context->entity->getId());
    }
}
