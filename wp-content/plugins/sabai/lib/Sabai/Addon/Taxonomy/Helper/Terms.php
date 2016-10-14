<?php
class Sabai_Addon_Taxonomy_Helper_Terms extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName)
    {
        $cache_id = 'taxonomy_terms_' . $bundleName;
        if (!$list = $application->getPlatform()->getCache($cache_id)) {
            $list = $term_ids = array();
            $terms = $application->Entity_Query('taxonomy')->propertyIs('term_entity_bundle_name', $bundleName)->sortByProperty('term_title')->fetch();
            foreach ($terms as $term) {
                $parent_id = (int)$term->getParentId();
                $list[$parent_id][$term->getId()] = array(
                    'id' => $term->getId(),
                    'name' => $term->getSlug(),
                    'title' => $term->getTitle(),
                    'summary' => $application->Summarize($term->getContent(), 100),
                    'url' => (string)$application->Entity_Url($term),
                    'fields' => $term->getFieldValues(),
                    'type' => $term->getType(),
                    'bundle_name' => $term->getBundleName(),
                    'bundle_type' => $term->getBundleType(),
                );
                $term_ids[$term->getId()] = $parent_id;
            }
            if (!empty($term_ids)) {
                $content_count = $application->getModel(null, 'Taxonomy')
                    ->getGateway('Term')
                    ->getContentCount(array_keys($term_ids));
                foreach ($content_count as $term_id => $_content_count) {
                    $parent_id = $term_ids[$term_id];
                    $list[$parent_id][$term_id]['count'] = $_content_count;
                }
            }
        }
        $application->getPlatform()->setCache($list, $cache_id);
        
        return $list;
    }
}