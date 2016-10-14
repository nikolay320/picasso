<?php
class Sabai_Addon_Directory_Helper_RelatedListings extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $listing, $settings)
    {
        $settings += array(
            'num' => 5,
            'sort' => 'post_published',
            'order' => 'DESC'
        );
        $query = $application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $listing->getBundleName())
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIsNot('post_id', $listing->getId());
        if (!empty($listing->directory_category)) {
            $category_ids = array();
            foreach ($listing->directory_category as $category) {
                $category_ids[] = $category->getId();
            }
            $query->fieldIsIn('directory_category', $category_ids);
        } else {
            $query->fieldIsNull('directory_category');
        }
        if (!empty($settings['claimed_only'])) {
            $query->fieldIsNotNull('directory_claim', 'claimed_by');
        }
        if (isset($settings['column'])) {
            $query->sortByField($settings['sort'], $settings['order'], $column);
        } elseif ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], $settings['order']);
        }
        
        return $query->fetch($settings['num']);
    }
}