<?php
require_once dirname(__FILE__) . '/Listings.php';

class Sabai_Addon_Directory_Controller_NearbyListings extends Sabai_Addon_Directory_Controller_Listings
{    
    protected function _createListingsQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createListingsQuery($context, $bundle)
            ->propertyIsNot('post_id', $context->entity->getId());
    }
    
    protected function _getAddonSettings(Sabai_Context $context, $addon)
    {
        $settings = parent::_getAddonSettings($context, $addon);
        $settings['claimed_only'] = false;
        $settings['hide_searchbox'] = true;
        $settings['center'] = $context->entity->directory_location[0]['lat'] . ',' . $context->entity->directory_location[0]['lng'];
        $settings['sort'] = 'distance';
        $settings['distance'] = 100;
        return $settings;
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->bundle;
    }
    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array(
            'distance' => array(
                'label' => __('Distance', 'sabai-directory'),
                'field_name' => 'directory_location',
                'field_type' => 'googlemaps_marker',
                'args' => array('lat' => $this->_center[0], 'lng' => $this->_center[1], 'is_mile' => $this->_settings['is_mile']),
            )
        );
    }
}