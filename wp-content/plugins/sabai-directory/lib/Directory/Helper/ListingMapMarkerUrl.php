<?php
class Sabai_Addon_Directory_Helper_ListingMapMarkerUrl extends Sabai_Helper
{
    protected $_categoryMapMarkerUrls = array();
    
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        $addon = $application->Entity_Addon($entity);
        if ($entity->directory_category) {
            if (!isset($this->_categoryMapMarkerUrls[$addon->getName()])) {
                $this->_categoryMapMarkerUrls[$addon->getName()] = $this->_getCategoryMapMarkerUrls($application, $addon->getCategoryBundleName());
            }
            foreach ($entity->directory_category as $category) {
                if (isset($this->_categoryMapMarkerUrls[$addon->getName()][$category->getId()])) {
                    return $this->_categoryMapMarkerUrls[$addon->getName()][$category->getId()];
                }
            }
        }
        return $addon->getConfig('map', 'icon');
    }
    
    protected function _getCategoryMapMarkerUrls(Sabai $application, $categoryBundleName)
    {
        $cache_id = $categoryBundleName . '_map_marker_urls';
        if (false === $ret = $application->getPlatform()->getCache($cache_id)) {
            $categories = $application->Entity_Query('taxonomy')
                ->propertyIs('term_entity_bundle_name', $categoryBundleName)
                ->fetch();
            foreach ($categories as $category) {
                if (isset($category->directory_map_marker[0]['name'])) {
                    $ret[$category->getId()] = $application->File_ThumbnailUrl($category->directory_map_marker[0]['name']);
                } else {
                    foreach (array_reverse($application->Taxonomy_Parents($category)) as $parent) {
                        $application->Entity_LoadFields($parent);
                        if (isset($parent->directory_map_marker[0]['name'])) {
                            $ret[$category->getId()] = $application->File_ThumbnailUrl($parent->directory_map_marker[0]['name']);
                        }
                    }
                }
            }
            $application->getPlatform()->setCache($ret, $cache_id, 86400);
        }
        return $ret;
    }
}
