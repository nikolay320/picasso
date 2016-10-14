<?php
require_once dirname(__FILE__) . '/AllListings.php';
class Sabai_Addon_Directory_Controller_GeoLocate extends Sabai_Addon_Directory_Controller_AllListings
{ 
    protected $_template = 'directory_listings_geolocate';
    
    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $ret = parent::_getUrlParams($context, $bundle);
        if (isset($context->sort)) {
            $ret['sort'] = $context->sort; // user original sort since "distance" gets removed
        }
        return $ret;
    }
}