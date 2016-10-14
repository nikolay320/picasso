<?php
class Sabai_Addon_Content_Helper_TrashPosts extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds, $type = Sabai_Addon_Content::TRASH_TYPE_OTHER, $reason = '', $trashedBy = null)
    {
        $trashed_at = time();
        $trashed_by = isset($trashedBy) ? $trashedBy : $application->getUser()->id;
        
        // Trash posts
        $entities = $entities_by_bundle = array();
        if (!is_array($entityIds) && $entityIds instanceof Sabai_Addon_Content_Entity) {
            $entity = $entityIds;
            if ($entity->isTrashed()) {
                // alreadh in trash
                return;
            }
            $application->Entity_Save($entity, array(
                'content_post_status' => Sabai_Addon_Content::POST_STATUS_TRASHED,
                'content_trashed' => array(
                    'trashed_at' => $trashed_at,
                    'trashed_by' => $trashed_by,
                    'type' => $type,
                    'reason' => $reason,
                    'prev_status' => $entity->getStatus(),
                ),
            ));
            $entities[$entity->getId()] = $entity;
            $entities_by_bundle[$entity->getBundleType()][$entity->getBundleName()][$entity->getId()] = $entity;
        } else { 
            foreach ($application->Entity_Entities('content', (array)$entityIds) as $entity) {
                if ($entity->isTrashed()) {
                    // alreadh in trash
                    continue;
                }
                $application->Entity_Save($entity, array(
                    'content_post_status' => Sabai_Addon_Content::POST_STATUS_TRASHED,
                    'content_trashed' => array(
                        'trashed_at' => $trashed_at,
                        'trashed_by' => $trashed_by,
                        'type' => $type,
                        'reason' => $reason,
                        'prev_status' => $entity->getStatus(),
                    ),
                ));
                $entities[$entity->getId()] = $entity;
                $entities_by_bundle[$entity->getBundleType()][$entity->getBundleName()][$entity->getId()] = $entity;
            }
        }
        
        if (empty($entities)) {
            return;
        }
        
        // Fetch child entities and trash them as well
        foreach ($application->Entity_Query('content')->fieldIsIn('content_parent', array_keys($entities))->fetch() as $child_entity) {
            $parent_id = $child_entity->getSingleFieldValue('content_parent')->getId();
            $trashed = @$child_entity->getFieldValue('content_trashed');
            if (!empty($trashed[0])) {
                // this child post is already in the trash, so overwrite the value of parent ID only 
                $application->Entity_Save($child_entity, array(
                    'content_post_status' => Sabai_Addon_Content::POST_STATUS_TRASHED,
                    'content_trashed' => array(
                        'trashed_at' => $trashed[0]['trashed_at'],
                        'trashed_by' => $trashed[0]['trashed_by'],
                        'type' => $trashed[0]['type'],
                        'prev_status' => $trashed[0]['prev_status'],
                        'parent_entity_id' => $parent_id,
                    ),
                ));
            } else {
                $application->Entity_Save($child_entity, array(
                    'content_post_status' => Sabai_Addon_Content::POST_STATUS_TRASHED,
                    'content_trashed' => array(
                        'trashed_at' => $trashed_at,
                        'trashed_by' => $trashed_by,
                        'type' => $type,
                        'prev_status' => $child_entity->getStatus(),
                        'parent_entity_id' => $parent_id,
                    ),
                ));
                $entities[$child_entity->getId()] = $child_entity;
                $entities_by_bundle[$child_entity->getBundleType()][$child_entity->getBundleName()][$child_entity->getId()] = $child_entity;
            }
        }
        $application->Action('content_posts_trashed', array($entities));
        foreach (array_keys($entities_by_bundle) as $bundle_type) {
            foreach (array_keys($entities_by_bundle[$bundle_type]) as $bundle_name) {
                $application->Action('content_' . $bundle_type . '_posts_trashed', array($bundle_name, $entities_by_bundle[$bundle_type][$bundle_name]));
            }
        }
    }
}