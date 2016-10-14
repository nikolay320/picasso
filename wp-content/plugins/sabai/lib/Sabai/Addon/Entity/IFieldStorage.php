<?php
interface Sabai_Addon_Entity_IFieldStorage
{
    /**
     * @param string $key
     * @return mixed
     */
    public function entityFieldStorageGetInfo($key = null);
    /**
     * @param string $entityType
     * @param int $bundleId
     * @param array $entities Array of entity field values indexed by entity IDs
     * @param array $fields Array of Sabai_Addon_Entity_Model_Field instances
     */
    public function entityFieldStorageSaveValues(Sabai_Addon_Entity_IEntity $entity, array $fieldValues);
    /**
     * @return array Array of field values indexed by entity IDs and field names
     * @param string $entityType
     * @param array $entityIds Array of entity IDs
     * @param array $fields Array of Sabai_Addon_Entity_Model_Field instances
     */
    public function entityFieldStorageFetchValues($entityType, array $entityIds, array $fields);
    /**
     * @param string $entityType
     * @param array $entityIds Array of entity IDs
     * @param array $fields Array of Sabai_Addon_Entity_Model_Field instances
     */
    public function entityFieldStoragePurgeValues($entityType, array $entityIds, array $fields);
    /**
     * @param array $fieldConfigs Array of Sabai_Addon_Entity_Model_FieldConfig instances
     * @param array $oldFieldConfigs Array of Sabai_Addon_Entity_Model_FieldConfig instances
     */
    public function entityFieldStorageUpdate(array $fieldConfigs, array $oldFieldConfigs);
    /**
     * @param string $entityType
     * @param array $fieldQuery Sabai_Addon_Entity_FieldQuery
     * @param int $limit
     * @param int $offset
     */
    public function entityFieldStorageQuery($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery, $limit = 20, $offset = 0);
    /**
     * @param string $entityType
     * @param array $fieldQuery Sabai_Addon_Entity_FieldQuery
     */
    public function entityFieldStorageQueryCount($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery);
}