<?php
class Sabai_Addon_Directory_Controller_RedirectToListing extends Sabai_Addon_Content_Controller_RedirectToParentPost
{    
    protected function _getRedirectUrl(Sabai_Context $context, Sabai_Addon_Entity_Entity $parentEntity)
    {
        if ($context->entity->getBundleType() === 'directory_listing_review') {
            // Add fragment part
            return $this->Entity_Url($parentEntity, '/reviews', array('sort' => 'newest', '__fragment' => 'sabai-entity-content-' . $context->entity->getId()));
        }
        if ($context->entity->getBundleType() === 'directory_listing_photo') {
            // Add fragment part
            return $this->Entity_Url($parentEntity, '/photos', array('photo_id' => $context->entity->getId()));
        }
    }
}