<?php
class Sabai_Addon_Entity_Helper_Entities extends Sabai_Helper
{
    public function help(Sabai $application, $entityType, $entityIds, $loadEntityFields = true, $preserveOrder = false)
    {
        $entities = array();
        if (empty($entityIds)) return $entities;

        foreach ($application->Entity_TypeImpl($entityType)->entityTypeGetEntitiesByIds($entityIds) as $entity) {
            $entities[$entity->getId()] = $entity;
        }
        // Load fields?
        if ($loadEntityFields) {
            $application->Entity_LoadFields($entityType, $entities);
        }
        // Preserve order
        if (!$preserveOrder) {
            return $entities;
        }
        // Re-order entities as requested
        $ret = array();
        foreach ($entityIds as $entity_id) {
            if (!isset($entities[$entity_id])) {
                continue;
            }
            $ret[$entity_id] = $entities[$entity_id];
        }

        return $ret;
    }
}