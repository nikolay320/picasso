<?php
class Sabai_Addon_Content_Helper_PublishPosts extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds, $childrenOnly = false, $status = Sabai_Addon_Content::POST_STATUS_PUBLISHED)
    {
        // Fetch entities
        $entities = $entities_by_bundle = array();
        if ($entityIds instanceof Sabai_Addon_Content_Entity) {
            if ($entityIds->isTrashed() || $entityIds->getStatus() === $status) {
                if (!$childrenOnly) return;
                
                $entities[$entityIds->getId()] = $entityIds;
            } else {
                $entities[$entityIds->getId()] = $entityIds;
                $entities_by_bundle[$entityIds->getBundleType()][$entityIds->getBundleName()][$entityIds->getId()] = $entityIds;
            }
        } elseif (is_array($entityIds)) {
            foreach ($entityIds as $key => $entity_id) {
                if ($entity_id instanceof Sabai_Addon_Content_Entity) {
                    unset($entityIds[$key]);
                    if ($entity_id->isTrashed() || $entity_id->getStatus() === $status) {
                        if (!$childrenOnly) continue;
                
                        $entities[$entity_id->getId()] = $entity_id;
                    } else {
                        $entities[$entity_id->getId()] = $entity_id;
                        $entities_by_bundle[$entity->getBundleType()][$entity_id->getBundleName()][$entity_id->getId()] = $entity_id;
                    }
                }
            }
            if (!empty($entityIds)) {
                foreach ($application->Entity_Entities('content', $entityIds) as $entity) {
                    if ($entity->isTrashed() || $entity->getStatus() === $status) {
                        if (!$childrenOnly) continue;
                        
                        $entities[$entity->getId()] = $entity;
                    } else {
                        $entities[$entity->getId()] = $entity;
                        $entities_by_bundle[$entity->getBundleType()][$entity->getBundleName()][$entity->getId()] = $entity;
                    }
                }
            }
        }
        
        if (empty($entities)) return;
        
        // Fetch child entities
        $child_entities = array();
        foreach ($application->Entity_Query('content')->fieldIsIn('content_parent', array_keys($entities))->fetch() as $child_entity) {
            if ($child_entity->isTrashed() || $child_entity->getStatus() === $status) continue;
          
            $child_entities[$child_entity->getId()] = $child_entity;
        }
        
        if ($childrenOnly) {
            if (empty($child_entities)) return;
            
            $entities = $child_entities;
        } else {
            $entities += $child_entities;
        }
        foreach (array_keys($entities) as $entity_id) {            
            $application->Entity_Save($entities[$entity_id], array('content_post_status' => $status));
        }
        
        $application->Action('content_posts_published', array($entities));
        foreach (array_keys($entities_by_bundle) as $bundle_type) {
            foreach (array_keys($entities_by_bundle[$bundle_type]) as $bundle_name) {
                $application->Action('content_' . $bundle_type . '_posts_published', array($bundle_name, $entities_by_bundle[$bundle_type][$bundle_name]));
            }
        }
    }
}