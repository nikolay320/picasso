<?php
require_once dirname(__FILE__) . '/Listings.php';

class Sabai_Addon_Directory_Controller_RelatedListings extends Sabai_Addon_Directory_Controller_Listings
{    
    protected function _createListingsQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = parent::_createListingsQuery($context, $bundle)
            ->propertyIsNot('post_id', $context->entity->getId());
        if (!empty($context->entity->directory_category)) {
            $category_ids = array();
            foreach ($context->entity->directory_category as $category) {
                $category_ids[] = $category->getId();
            }
            $query->fieldIsIn('directory_category', $category_ids);
        } else {
            $query->fieldIsNull('directory_category');
        }
        return $query;
    }
    
    protected function _getAddonSettings(Sabai_Context $context, $addon)
    {
        $settings = parent::_getAddonSettings($context, $addon);
        $settings['claimed_only'] = false;
        $settings['hide_searchbox'] = true;
        return $settings;
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->bundle;
    }
}