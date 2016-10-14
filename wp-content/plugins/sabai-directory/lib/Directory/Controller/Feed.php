<?php
class Sabai_Addon_Directory_Controller_Feed extends Sabai_Addon_Content_Controller_Feed
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
                $bundle_names = array($this->getAddon()->getListingBundleName());
                break;
            case 'review':
                $bundle_names = array($this->getAddon()->getReviewBundleName());
                break;
            default:
                $bundle_names = array($this->getAddon()->getListingBundleName(), $this->getAddon()->getReviewBundleName());
                break;
        }
        return parent::_createQuery($context)
            ->propertyIsIn('post_entity_bundle_name', $bundle_names);
    }
    
    protected function _getDescription(Sabai_Context $context)
    {
        switch ($this->_type) {
            case 'listing':
                $title = __('The most recent %d listings in %s', 'sabai-directory');
                break;
            case 'review':
                $title = __('The most recent %d reviews in %s', 'sabai-directory');
                break;
            default:
                $title = __('The most recent %d listings and reviews in %s', 'sabai-directory');
                break;
        }
        return sprintf($title, $this->_numItems, $this->getAddon()->getTitle('directory'));
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
    
    protected function _getItemExtras(Sabai_Context $context, Sabai_Addon_Entity_Entity $entity)
    {
        switch ($entity->getBundleType()) {
            case 'directory_listing':
                if (($latlng = $entity->getSingleFieldValue('directory_location', 'lat')) && $latlng['lat'] && $latlng['lng']) {
                    return array('georss:point' => $latlng['lat'] . ' ' . $latlng['lng']);
                }
            default:
                return parent::_getItemExtras($context, $entity);
        }
    }
    
    protected function _getNamespaces(Sabai_Context $context)
    {
        return array('georss' => 'http://www.georss.org/georss');
    }
}