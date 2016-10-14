<?php
class Sabai_Addon_Entity_Helper_Save extends Sabai_Helper
{
    public function help(Sabai $application, $bundleOrEntity, array $values, array $extraArgs = array(), Sabai_UserIdentity $identity = null)
    {
        return $bundleOrEntity instanceof Sabai_Addon_Entity_IEntity
            ? $this->_updateEntity($application, $bundleOrEntity, $values, $extraArgs)
            : $this->_createEntity($application, $bundleOrEntity, $values, $extraArgs, $identity);
    }
    
    protected function _createEntity(Sabai $application, $bundleName, array $values, array $extraArgs = array(), Sabai_UserIdentity $identity = null)
    {     
        if ($bundleName instanceof Sabai_Addon_Entity_Model_Bundle) {
            $bundle = $bundleName;
        } else {
            if (!$bundle = $application->Entity_Bundle($bundleName)) {
                throw new Sabai_RuntimeException('Invalid bundle: ' . $bundleName);
            }
        }
        // Notify that an entity is being created
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, &$values, &$extraArgs), 'before_create');
        // Extract field values for saving
        $values = $this->_extractFieldValues($application, $fields = $bundle->Fields->with('FieldConfig'), $values, null, $extraArgs);
        // Notify that an entity is being created, with extracted fields values
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, &$values, &$extraArgs), 'create');
        // Save entity
        $entity = $this->_saveEntity($application, $bundle, $values, $fields, null, $identity);
        // Notify that an entity has been created
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, $entity, $values, &$extraArgs), 'after_create');
        // Load entity fields
        $application->Entity_LoadFields($entity, null, null, true);
        // Notify that an entity has been saved
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, $entity, $values, $extraArgs), 'create', 'success');

        return $entity;
    }

    protected function _updateEntity(Sabai $application, Sabai_Addon_Entity_Entity $entity, array $values, array $extraArgs = array())
    {
        if (!$bundle = $application->Entity_Bundle(isset($extraArgs['bundle']) ? $extraArgs['bundle'] : $entity)) {
            throw new Sabai_RuntimeException('Invalid bundle.');
        }
        if ($entity->isFromCache()) {
            // this entity was loaded from cache, so load again from storage to make sure the current values are available
            if (!$entity = $application->Entity_Entity($bundle->entitytype_name, $entity->getId(), false)) {
                throw new Sabai_RuntimeException('Invalid entity.');
            }
        }
        // Make sure all the fields are loaded
        $application->Entity_LoadFields($entity, null, null, false, false);
        // Notify that an entity is being updated
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, $entity, &$values, &$extraArgs), 'before_update');
        // Extract modified field values for saving
        $values = $this->_extractFieldValues($application, $fields = $bundle->Fields->with('FieldConfig'), $values, $entity, $extraArgs);
        // Notify that an entity is being updated, with extracted field values
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, $entity, &$values, &$extraArgs), 'update');
        // Save entity
        $updated_entity = $this->_saveEntity($application, $bundle, $values, $fields, $entity);
        // Notify that an entity has been updated
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, $updated_entity, $entity, $values, &$extraArgs), 'after_update');
        // Clear cached entity fields
        $application->Entity_FieldCacheImpl()->entityFieldCacheRemove($updated_entity->getType(), array($updated_entity->getId()));
        // Field values may have changed, so reload entity
        $application->Entity_LoadFields($updated_entity, null, null, true);
        // Notify that an entity has been saved
        $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, $updated_entity, $entity, $values, $extraArgs), 'update', 'success');
        
        if ($updated_entity->getBundleName() !== $entity->getBundleName()) {
            // Notify that entity bundle has been changed
            $this->_invokeEntityEvents($application, $bundle->entitytype_name, $bundle->type, array($bundle, $updated_entity, $entity, $values, $extraArgs), 'change', 'bundle_success');
        }
        
        return $updated_entity;
    }
    
    private function _extractFieldValues(Sabai $application, $fields, array $fieldValues, Sabai_Addon_Entity_IEntity $entity = null, array $extraArgs = array())
    {     
        // Extract field values to save
        $ret = array();
        foreach ($fields as $field) {
            $field_name = $field->getFieldName();
            if (null === $field_value = @$fieldValues[$field_name]) {
                continue;
            }
            
            if (!$field->isPropertyField()) {
                // Always pass value as array
                if (!is_array($field_value) || !array_key_exists(0, $field_value)) {
                    $field_value = array($field_value);
                } else {
                    unset($field_value['_add']); // remove add more button value
                }
                if (!$field_type_impl = $application->Field_TypeImpl($field->getFieldType(), true)) continue;
                // Get current value if this is an update
                $current_field_value = isset($entity) ? $entity->getFieldValue($field_name) : null;
                // Let the field type addon for the field to work on values before saving to the storage
                $field_value = $field_type_impl->fieldTypeOnSave($field, $field_value, $current_field_value);
                if (!is_array($field_value)) {
                    continue;
                }
                // Is the maximum number of items for this field limited?
                $max_num_values = isset($extraArgs['entity_field_max_num_values'][$field_name]) ? $extraArgs['entity_field_max_num_values'][$field_name] : $field->getFieldMaxNumItems();
                if (!is_numeric($max_num_values)) {
                    $max_num_values = 10; // defaults to 10
                }
                if ($max_num_values && count($field_value) > $max_num_values) {
                    $field_value = array_slice($field_value, 0, $max_num_values);
                }
                // If this is an update, make sure that the new value is different from the existing one
                if (isset($entity)) {
                    $current_field_value = @$entity->getFieldValue($field_name);
                    if ($current_field_value !== null
                        && !$field_type_impl->fieldTypeIsModified($field, $field_value, $current_field_value)
                    ) {
                        // the value hasn't changed, so skip this field
                        continue;
                    }
                }
                $ret[$field_name] = $field_value;
            } else {
                if (is_array($field_value) && array_key_exists(0, $field_value)) {
                    $field_value = $field_value[0];
                }
                // If this is an update, make sure that the new value is different from the existing one
                if (isset($entity)) {
                    if (!$entity->isPropertyModified($field_name, $field_value)) {
                        // the value hasn't changed, so skip this field
                        continue;
                    }
                }
                $ret[$field_name] = $field_value;
            }
        }
        
        return $ret;
    }

    private function _saveEntity(Sabai $application, Sabai_Addon_Entity_Model_Bundle $bundle, array $fieldValues, $fields = null, Sabai_Addon_Entity_IEntity $entity = null, Sabai_UserIdentity $identity = null)
    {     
        // Extract field values to save
        $field_values_by_storage = $properties = array();
        if (!isset($fields)) {
            $fields = $bundle->Fields->with('FieldConfig');
        }
        foreach ($fields as $field) {
            $field_name = $field->getFieldName();
            if (!isset($fieldValues[$field_name])) {
                continue;
            }

            if (!$field->isPropertyField()) {
                $field_values_by_storage[$field->getFieldStorage()][$field_name] = $fieldValues[$field_name];
            } else {
                $properties[$field_name] = $fieldValues[$field_name];
            }
        }

        // Save entity
        $entity_type_impl = $application->Entity_TypeImpl($bundle->entitytype_name);
        if (!isset($entity)) {
            if (!isset($identity)) {
                $identity = $application->getUser()->getIdentity();
            }
            $ret = $entity_type_impl->entityTypeCreateEntity($bundle, $properties, $identity);
        } else {
            $ret = $entity_type_impl->entityTypeUpdateEntity($entity, $bundle, $properties);
        }

        // Save fields
        foreach (array_keys($field_values_by_storage) as $field_storage) {
            $application->Entity_FieldStorageImpl($field_storage)
                ->entityFieldStorageSaveValues($ret, $field_values_by_storage[$field_storage]);
        }
        
        return $ret;
    }
    
    private function _invokeEntityEvents(Sabai $application, $entityType, $bundleType, array $params, $prefix, $suffix = '')
    {
        $prefix = 'entity_' . $prefix;
        $suffix = $suffix !== '' ? 'entity_' . $suffix : 'entity';
        $application->Action($prefix . '_' . $suffix, $params);
        $application->Action($prefix . '_' . $entityType . '_' . $suffix, $params);
        $application->Action($prefix . '_' . $entityType . '_' . $bundleType . '_' . $suffix, $params);
    }
}