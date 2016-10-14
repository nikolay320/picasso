<?php
class Sabai_Addon_Entity_Helper_LoadFields extends Sabai_Helper
{    
    public function help(Sabai $application, $entityType, array $entities = null, $fieldStorage = null, $force = false, $cache = true)
    {
        if ($entityType instanceof Sabai_Addon_Entity_IEntity) {
            if ($entityType->isFieldsLoaded() && !$force) {
                return;
            }
            $entities = array($entityType->getId() => $entityType);
            $entityType = $entityType->getType();
        }
        if (!$force) {
            $entities_loaded = $application->Entity_FieldCacheImpl()->entityFieldCacheLoad($entityType, $entities);
            $entities_to_load = array_diff_key($entities, array_flip($entities_loaded));
        } else {
            $entities_to_load = $entities;
        }
        if (!empty($entities_to_load)) {
            $this->_loadEntityFields($application, $entityType, $entities_to_load, $fieldStorage, $cache);
            if ($cache) {
                try {
                    $application->Entity_FieldCacheImpl()->entityFieldCacheSave($entityType, $entities_to_load);
                } catch (Exception $e) {
                    $application->LogError($e);
                }
            }
        }
    }

    protected function _loadEntityFields(Sabai $application, $entityType, array $entities, $fieldStorage, $cache)
    {
        $entities_by_bundle = $field_values_by_bundle = $field_types_by_bundle = $fields_by_bundle = array();
        foreach ($entities as $entity_id => $entity) {     
            $entities_by_bundle[$entity->getBundleName()][$entity_id] = $entity;
        }
        $bundles = $application->Entity_BundleCollection(array_keys($entities_by_bundle))->with('Fields', 'FieldConfig');
        if (isset($fieldStorage)) {
            // Single field storage, probably called via fetchEntities()
            foreach ($bundles as $bundle) {
                foreach ($bundle->Fields as $field) {
                    if (!$field->FieldConfig) continue;

                    $fields_by_bundle[$bundle->name][$field->getFieldName()] = $field;
                    $field_types_by_bundle[$bundle->name][$field->getFieldName()] = $field->getFieldType();
                }
                $field_values_by_bundle[$bundle->name] = $application->Entity_FieldStorageImpl($fieldStorage)
                    ->entityFieldStorageFetchValues($entityType, array_keys($entities_by_bundle[$bundle->name]), array_keys($fields_by_bundle[$bundle->name]));
            }
        } else {
            $fields_by_storage = array();
            foreach ($bundles as $bundle) {
                foreach ($bundle->Fields as $field) {
                    if (!$field->FieldConfig) continue;

                    $fields_by_bundle[$bundle->name][$field->getFieldName()] = $field;
                    $field_types_by_bundle[$bundle->name][$field->getFieldName()] = $field->getFieldType();
                    $fields_by_storage[$field->getFieldStorage()][$bundle->name][$field->getFieldName()] = $field;
                }
            }
            foreach ($fields_by_storage as $field_storage => $bundle_fields) {
                foreach ($bundle_fields as $bundle_name => $fields) {
                    $field_values_by_bundle[$bundle_name] = $application->Entity_FieldStorageImpl($field_storage)
                        ->entityFieldStorageFetchValues($entityType, array_keys($entities_by_bundle[$bundle_name]), array_keys($fields));
                }
            }
        }
        // Load field values
        foreach ($bundles as $bundle) {
            foreach ($entities_by_bundle[$bundle->name] as $entity_id => $entity) {
                $entity_field_values = array();
                foreach ($bundle->Fields as $field) {
                    if ($field->isPropertyField()) continue; // do not call fieldTypeOnLoad() on property fields
                    
                    if (!$ifield_type = $application->Field_TypeImpl($field->getFieldType(), true)) continue;
                    
                    // Check whether or not the value for this field is cacheable
                    if ($cache && false === $ifield_type->fieldTypeGetInfo('cacheable')) continue;

                    $field_name = $field->getFieldName();
                    if (null === $values = @$field_values_by_bundle[$bundle->name][$entity_id][$field_name]) {
                        if ($ifield_type->fieldTypeGetInfo('load_empty')) {
                            $values = array();
                        }
                    }
                    if (null !== $values) {
                        // Let the field type addon for each field to work on values on load
                        $ifield_type->fieldTypeOnLoad($field, $values, $entity); 
                    }
                    $entity_field_values[$field->getFieldWeight()][$field_name] = $values;
                }
                // Reorder all fields by weight
                if (!empty($entity_field_values)) {
                    ksort($entity_field_values);
                    $_entity_field_values = array();
                    foreach (array_keys($entity_field_values) as $weight) {
                        $_entity_field_values += $entity_field_values[$weight];
                    }
                    $entity_field_values = $_entity_field_values;
                }
                // Allow other add-ons to filter entity field values 
                $entity_field_values = $application->Filter('entity_load_field_values', $entity_field_values, array($entity, $bundle, $fields_by_bundle[$bundle->name], $cache));
                $entity->initFields($entity_field_values, $field_types_by_bundle[$bundle->name]);
            }
        }
    }
}