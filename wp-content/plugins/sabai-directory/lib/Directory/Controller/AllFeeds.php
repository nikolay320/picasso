<?php
class Sabai_Addon_Directory_Controller_AllFeeds extends Sabai_Addon_Content_Controller_Feed
{
    protected $_type;
    
    protected function _doExecute(Sabai_Context $context)
    {
        $this->_type = $context->getRequest()->asStr('type', '', array('listing', 'review'));
        parent::_doExecute($context);
    }

    protected function _createQuery(Sabai_Context $context)
    {
        switch ($this->_type) {
            case 'listing':
                $bundle_types = array('directory_listing');
                break;
            case 'review':
                $bundle_types = array('directory_listing_review');
                break;
            default:
                $bundle_types = array('directory_listing', 'directory_listing_review');
                break;
        }
        return parent::_createQuery($context)
            ->propertyIsIn('post_entity_bundle_type', $bundle_types);
    }
        
    protected function _getDescription(Sabai_Context $context)
    {
        switch ($this->_type) {
            case 'listing':
                $title = __('The most recent %d listings', 'sabai-directory');
                break;
            case 'review':
                $title = __('The most recent %d reviews', 'sabai-directory');
                break;
            default:
                $title = __('The most recent %d listings and reviews', 'sabai-directory');
                break;
        }
        return sprintf($title, $this->_numItems);
    }
    
    protected function _getLink(Sabai_Context $context)
    {
        return $this->Url($context->getRoute(), $this->_type ? array('type' => $this->_type) : array());
    }
        
    protected function _getItemTitle(Sabai_Context $context, Sabai_Addon_Entity_Entity $entity)
    {
        switch ($entity->getBundleType()) {
            case 'directory_listing_review':
                $listing = $this->Content_ParentPost($entity);
                return $listing ? sprintf('%s - Review of %s (%.1f/5)', $entity->getTitle(), $listing->getTitle(), $entity->directory_rating['']) : '';
            default:
                return $entity->getTitle();
        }
    }
}