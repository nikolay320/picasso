<?php
interface Sabai_Addon_Entity_IFieldCache
{
    /**
     * Saves field values of given entities.
     * @param string $entityType
     * @param array $entities Array of Sabai_Addon_Entity_IEntity instances
     */
    public function entityFieldCacheSave($entityType, array $entities);
    /**
     * Loads cached field values of given entities.
     * @return array Array of entities that have field values loaded, indexed by entity ID
     * @param string $entityType
     * @param array $entities Array of Sabai_Addon_Entity_IEntity instances
     */
    public function entityFieldCacheLoad($entityType, array $entities);
    /**
     * Removes cached field values of given entities.
     * @param string $entityType
     * @param array $entityIds Array of entity IDs
     */
    public function entityFieldCacheRemove($entityType, array $entityIds);
    /**
     * Removes all cache for a specified entity type
     * @param string $entityType
     */
    public function entityFieldCacheClean($entityType = null);
}