<?php
class Sabai_Addon_Content_Helper_DeletePosts extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds)
    {
        // Fetch entities
        $entities = array();
        if ($entityIds instanceof Sabai_Addon_Content_Entity) {
            $entities[$entityIds->getId()] = $entityIds;
        } elseif (is_array($entityIds)) {
            foreach ($entityIds as $key => $entity_id) {
                if ($entity_id instanceof Sabai_Addon_Content_Entity) {
                    $entities[$entity_id->getId()] = $entity_id;
                    unset($entityIds[$key]);
                }
            }
            if (!empty($entityIds)) {
                foreach ($application->Entity_Entities('content', $entityIds) as $entity) {
                    $entities[$entity->getId()] = $entity;
                }
            }
        }
        
        if (empty($entities)) {
            return;
        }
        
        // Fetch child entities
        foreach ($application->Entity_Query('content')->fieldIsIn('content_parent', array_keys($entities))->fetch() as $child_entity) {
            $entities[$child_entity->getId()] = $child_entity;
        }
        
        // Delete all
        $application->getAddon('Entity')->deleteEntities('content', $entities);
    }
}