<?php
interface Sabai_Addon_Entity_IType
{
    /**
     * @return mixed Array if no key supplied
     * @param string $key
     */
    public function entityTypeGetInfo($key = null);
    /**
     * @return Sabai_Addon_Entity_IEntity
     * @param int $entityId
     */
    public function entityTypeGetEntityById($entityId);
    /**
     * @return Traversable Instances of Sabai_Addon_Entity_IEntity
     * @param array $entityIds
     */
    public function entityTypeGetEntitiesByIds(array $entityIds);
    /**
     * @return Sabai_Addon_Entity_IEntity
     */
    public function entityTypeCreateEntity(Sabai_Addon_Entity_Model_Bundle $bundle, array $properties, SabaiFramework_User_Identity $identity);
    /**
     * @param Sabai_Addon_Entity_IEntity $entity
     * @param Sabai_Addon_Entity_Model_Bundle $bundle
     * @return Sabai_Addon_Entity_IEntity
     */
    public function entityTypeUpdateEntity(Sabai_Addon_Entity_IEntity $entity, Sabai_Addon_Entity_Model_Bundle $bundle, array $properties);
    /**
     * @param array $entities Array of Sabai_Addon_Entity_IEntity indexed by entity ID
     */
    public function entityTypeDeleteEntities(array $entities);
    /**
     * @return Traversable Instances of Sabai_Addon_Entity_IEntity
     * @param string $keyword
     * @param int $bundle
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    public function entityTypeSearchEntitiesByBundle($keyword, $bundle, $limit = 0, $offset = 0, $sort = null, $order = null);
}