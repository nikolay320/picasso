<?php
require_once dirname(__FILE__) . '/Listings.php';

class Sabai_Addon_Directory_Controller_UserListings extends Sabai_Addon_Directory_Controller_Listings
{    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->startCriteriaGroup('OR')
                ->startCriteriaGroup()
                    ->propertyIs('post_user_id', $context->identity->id)
                    ->fieldIsNull('directory_claim', 'claimed_by')
                ->finishCriteriaGroup()
                ->startCriteriaGroup()
                    ->fieldIs('directory_claim', $context->identity->id, 'claimed_by')
                    ->fieldIsOrGreaterThan('directory_claim', time(), 'expires_at')
                ->finishCriteriaGroup()
            ->finishCriteriaGroup();
    }
    
    protected function _getAddonSettings(Sabai_Context $context, $addon)
    {
        $settings = parent::_getAddonSettings($context, $addon);
        $settings['claimed_only'] = false;
        $settings['hide_searchbox'] = true; // hide for now since the autocomplete feature does not filter by user posts
        return $settings;
    }
}