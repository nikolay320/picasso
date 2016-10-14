<?php
class Sabai_Addon_Directory_Controller_LocationList extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('system_list');
        $context->list = array();
        
        $column = $context->getRequest()->asStr('column', 'state', array('zip', 'city', 'country'));
        
        if (($addon_name = $context->getRequest()->asStr('addon'))
            && $this->isAddonLoaded($addon_name)
            && ($addon = $this->getAddon($addon_name))
            && $addon instanceof Sabai_Addon_Directory
        ) {
            $cache_id = 'directory_location_' . $addon_name . '_' . $column;
            $bundle = $addon->getListingBundleName();
        } else {
            $cache_id = 'directory_location_' . $column;
        }
        if (false === $context->list = $this->getPlatform()->getCache($cache_id)) {
            $context->list = array();
            $query = $this->Entity_Query('content')
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->groupByField('directory_location', $column, 'DESC');
            if (isset($bundle)) {
                $query->propertyIs('post_entity_bundle_name', $bundle);
            } else {
                $query->propertyIs('post_entity_bundle_type', 'directory_listing');
            }
            $counts = $query->count(100);
            foreach (array_keys($counts) as $value) {
                if (strlen($value)) {
                    $context->list[] = array('value' => $value);
                }
            }
            $this->getPlatform()->setCache($context->list, $cache_id);
        }
    }
}
