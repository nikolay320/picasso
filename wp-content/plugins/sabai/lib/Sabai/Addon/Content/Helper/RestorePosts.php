<?php
class Sabai_Addon_Content_Helper_RestorePosts extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds)
    {
        $entities = $entities_by_bundle = $prev_status = array();
        
        // Restore
        if (!is_array($entityIds) && $entityIds instanceof Sabai_Addon_Content_Entity) {
            $entity = $entityIds;
            $application->Entity_LoadFields($entity); // make sure all fields are loaded before checking the trashed
            if (false === $previous_status = $this->_isRestoreable($entity)) {
                return;
            }
            $entities[$entity->getId()] = $entity;
            $entities_by_bundle[$entity->getBundleType()][$entity->getBundleName()][$entity->getId()] = $entity;
            $prev_status[$entity->getId()] = $previous_status;
        } else {
            foreach ($application->Entity_Entities('content', (array)$entityIds) as $entity) {
                if (false === $previous_status = $this->_isRestoreable($entity)) {
                    return;
                }
                $entities[$entity->getId()] = $entity;
                $entities_by_bundle[$entity->getBundleType()][$entity->getBundleName()][$entity->getId()] = $entity;
                $prev_status[$entity->getId()] = $previous_status;
            }
        }
        
        // No posts have been restored
        if (empty($entities)) return;
        
        // Fetch child posts in the trash
        $child_posts = $application->Entity_Query('content')
            ->fieldIsIn('content_trashed', array_keys($entities), 'parent_entity_id')
            ->fetch();
        
        // Restore child posts
        foreach ($child_posts as $entity) {
            $entities[$entity->getId()] = $entity;
            $entities_by_bundle[$entity->getBundleType()][$entity->getBundleName()][$entity->getId()] = $entity;
            $prev_status[$entity->getId()] = $entity->getSingleFieldValue('content_trashed', 'prev_status');
        }
        
        foreach (array_keys($entities) as $entity_id) {
            $application->Entity_Save($entities[$entity_id], array(
                'content_post_status' => $prev_status[$entity_id],
                'content_trashed' => false // delete from storage
            ));
        }
        
        $application->Action('content_posts_restored', array($entities));
        foreach (array_keys($entities_by_bundle) as $bundle_type) {
            foreach (array_keys($entities_by_bundle[$bundle_type]) as $bundle_name) {
                $application->Action('content_' . $bundle_type . '_posts_restored', array($bundle_name, $entities_by_bundle[$bundle_type][$bundle_name]));
            }
        }
    }
    
    private function _isRestoreable(Sabai_Addon_Content_Entity $entity)
    {
        $trashed = $entity->getFieldValue('content_trashed');
        if (!isset($trashed[0]['trashed_at'])) {
            // Trash info does not exist for some reason. Force status to be published
            return Sabai_Addon_Content::POST_STATUS_PUBLISHED;
        }
        if (!empty($trashed[0]['parent_entity_id'])) {
            // This post has its parent post also trashed, so it must be restored when restoring the parent post
            return false;
        }
        if ($trashed[0]['prev_status'] === Sabai_Addon_Content::POST_STATUS_TRASHED) {
            // In case for some reason the previous status is trashed, we force it to become published
            return Sabai_Addon_Content::POST_STATUS_PUBLISHED;
        }
        return $trashed[0]['prev_status'];
    }
}