<?php
class Sabai_Addon_Taxonomy_Helper_UpdateContentCount extends Sabai_Helper
{
    public function help(Sabai $application, array $terms, Sabai_Addon_Entity_Model_Bundle $contentBundle)
    {
        $taxonomy_bundles = array();
        foreach ($terms as $taxonomy_type => $taxonomy_terms) {
            // Count the total number of child entity for each term
            $counts = $application->Entity_Query('content')
                ->fieldIsIn($taxonomy_type, array_keys($taxonomy_terms))
                ->propertyIs('post_entity_bundle_name', $contentBundle->name)
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->groupByField($taxonomy_type)
                ->count();        
            
            // Update content count for each taxonomy term 
            foreach ($taxonomy_terms as $term) {            
                $current_content_count = (array)@$term->getFieldValue('taxonomy_content_count');
                if (empty($counts[$term->getId()])) {
                    unset($current_content_count[$contentBundle->type]);
                } else {
                    // Set the new content count
                    $current_content_count[$contentBundle->type] = $counts[$term->getId()];
                }
                // Create an array of content count for saving
                $counts_for_save = array(false);
                foreach ($current_content_count as $content_bundle_type => $content_count) {                
                    $counts_for_save[] = array('content_bundle_name' => $content_bundle_type, 'value' => $content_count);
                }
                $application->Entity_Save($term, array('taxonomy_content_count' => $counts_for_save));
                
                $taxonomy_bundles[$term->getBundleName()] = $term->getBundleName();
            }
        }
        
        foreach ($taxonomy_bundles as $taxonomy_bundle) {
            $application->getPlatform()->deleteCache('taxonomy_terms_' . $taxonomy_bundle); 
        }
    }
}