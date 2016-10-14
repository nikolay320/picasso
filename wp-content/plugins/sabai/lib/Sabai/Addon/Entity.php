<?php
class Sabai_Addon_Entity extends Sabai_Addon
    implements Sabai_Addon_Entity_IFieldStorages,
               Sabai_Addon_Entity_IFieldCache,
               Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    const FIELD_REALM_ALL = 0, FIELD_REALM_ENTITY_TYPE_DEFAULT = 1, FIELD_REALM_BUNDLE_DEFAULT = 2;

    private static $_reservedBundleNames = array('users', 'my', 'flagged', 'add', 'comments', 'vote');

    public function systemGetAdminRoutes()
    {
        return array();
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {

    }

    public function entityGetFieldStorageNames()
    {
        return array('sql');
    }

    public function entityGetFieldStorage($storageName)
    {
        switch ($storageName) {
            case 'sql':
                return new Sabai_Addon_Entity_FieldStorage_Sql($this->_application, $storageName);
        }
    }

    public function onEntityITypesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        if (!$new_entity_types = $addon->entityGetTypeNames()) return;
        
        $this->_application->getPlatform()->deleteCache('entity_types');
        $this->_createEntityTypes($addon, $new_entity_types);
        $this->_setEntityTypes(array($addon->getName() => $new_entity_types) + $this->_getEntityTypes());
    }

    public function onEntityITypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $entity_types = $this->_getEntityTypes();
        if (!isset($entity_types[$addon->getName()])) return;
        
        $this->_application->getPlatform()->deleteCache('entity_types');
        $this->_deleteEntityTypes($addon, $entity_types[$addon->getName()]);
        unset($entity_types[$addon->getName()]);
        $this->_setEntityTypes($entity_types);
    }

    public function onEntityITypesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('entity_types');
        $entity_types = $this->_getEntityTypes();
        if (!$names = $addon->entityGetTypeNames()) {
            if (isset($entity_types[$addon->getName()])) {
                $this->_deleteEntityTypes($addon, $entity_types[$addon->getName()]);
                unset($entity_types[$addon->getName()]);
            }
        } else {
            if (!isset($entity_types[$addon->getName()])) {
                $this->_createEntityTypes($addon, $names);
                $entity_types[$addon->getName()] = $names;
            } else {
                $old_entity_types = $entity_types[$addon->getName()];
                $entity_types[$addon->getName()] = array();
                if ($new_entity_types = array_diff($names, $old_entity_types)) {
                    $this->_createEntityTypes($addon, $new_entity_types);
                    foreach ($new_entity_types as $new_entity_type) {
                        $entity_types[$addon->getName()][] = $new_entity_type;
                    }
                }
                if ($current_entity_types = array_intersect($old_entity_types, $names)) {
                    $this->_updateEntityTypes($addon, $current_entity_types);
                    foreach ($current_entity_types as $current_entity_type) {
                        $entity_types[$addon->getName()][] = $current_entity_type;
                    }
                }
                if ($deleted_entity_types = array_diff($old_entity_types, $names)) {
                    $this->_deleteEntityTypes($addon, $deleted_entity_types);
                }
            }
        }
        $this->_setEntityTypes($entity_types);
    }

    private function _createEntityTypes(Sabai_Addon $addon, array $entityTypes)
    {
        $model = $this->getModel();
        $bundles = $entity_types = array();
        foreach ($entityTypes as $name) {
            // Entity type name must start with its add-on name
            if (0 !== strpos($name, strtolower($addon->getName()))) {
                continue;
            }
            $info = $addon->entityGetType($name)->entityTypeGetInfo();
            // Create entity type specific fields if any
            if (!empty($info['properties'])) {
                foreach ($info['properties'] as $property_name => $property_info) {
                    $this->_createEntityPropertyFieldConfig($name, $property_name, $property_info);
                }
            }
            if (!empty($info['bundles'])) $bundles[$name] = $info['bundles'];
            $entity_types[] = $name;
        }
        $model->commit();

        $this->_application->Action('entity_create_entity_types_success', array($entity_types));

        // Create bundles associated with the entity type if any
        if (!empty($bundles)) {
            $new_fields = array();
            foreach ($bundles as $entity_type => $entity_type_bundles) {
                $this->createEntityBundles($addon, $entity_type, $entity_type_bundles, false, $new_fields);
            }
            $model->commit();
            // Update fields
            if (!empty($new_fields)) $this->createFieldStorage($new_fields);
        }
    }

    private function _updateEntityTypes(Sabai_Addon $addon, $entityTypes)
    {
        $bundles = $new_fields_by_entity_type = array();
        foreach ($entityTypes as $entity_type) {
            $info = $addon->entityGetType($entity_type)->entityTypeGetInfo();
            // Update property fields
            $current_fields = $this->getModel('FieldConfig')
                ->entitytypeName_is($entity_type)
                ->property_isNot('')
                ->fetch();
            if (!empty($info['properties'])) {
                $fields_already_installed = array();
                foreach ($current_fields as $current_field) {
                    if (!isset($info['properties'][$current_field->property])) {
                        $current_field->markRemoved();
                    } else {
                        if (isset($info['properties'][$current_field->property]['settings'])) {
                            // Only update settings
                            $current_field->settings = $info['properties'][$current_field->property]['settings'];
                        }
                        $fields_already_installed[] = $current_field->property;
                    }
                }
                // Create newly added fields
                foreach (array_diff(array_keys($info['properties']), $fields_already_installed) as $property_name) {
                    if ($new_field = $this->_createEntityPropertyFieldConfig($entity_type, $property_name, $info['properties'][$property_name])) {
                        $new_fields_by_entity_type[$entity_type][$new_field->name] = array($new_field, $info['properties'][$property_name]);
                    }
                }
            } else {
                foreach ($current_fields as $current_field) {
                    $current_field->markRemoved();
                }
            }
            if (!empty($info['bundles'])) $bundles[$entity_type] = $info['bundles'];
        }
        $this->getModel()->commit();

        $this->_application->Action('entity_update_entity_types_success', array($entityTypes));

        // Update bundles associated with the entity type if any
        if (!empty($bundles)) {
            foreach ($bundles as $entity_type => $entity_type_bundles) {
                $bundles[$entity_type] = $this->updateEntityBundles($addon, $entity_type, $entity_type_bundles);
            }
        }

        // Add new entity type fields to current active bundles
        if (!empty($new_fields_by_entity_type)) {
            $this->_application->Field_Types(false); // reload field types
            foreach ($new_fields_by_entity_type as $entity_type => $new_entity_type_fields) {
                foreach ($this->getModel('Bundle')->entitytypeName_is($entity_type)->fetch() as $bundle) {
                    if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
                    
                    foreach ($new_entity_type_fields as $field_name => $field_data) {
                        $this->_createEntityPropertyField($bundle, $field_data[0], $field_data[1]);
                    }
                }
            }
            $this->getModel()->commit();
        }
    }

    private function _deleteEntityTypes(Sabai_Addon $addon, array $entityTypes)
    {        
        $removed_fields = array();
        foreach ($entityTypes as $entity_type) {
            $this->deleteEntityBundles($addon, $entity_type, null, $removed_fields);
            $this->entityFieldCacheClean($entity_type);
        }
        $this->getModel()->commit();
        $this->_application->Action('entity_delete_entity_types_success', array($entityTypes));
        // Update fields
        if (!empty($removed_fields)) $this->deleteFieldStorage($removed_fields);
    }
    
    protected function _getEntityTypes()
    {
        $entity_types = $this->_application->getPlatform()->getOption('entity_types');
        if (!is_array($entity_types)) {
            // Fix for version lower than 1.3.0
            $entity_types = array('Content' => array('content'), 'Taxonomy' => array('taxonomy'));
        }
        return $entity_types;
    }
    
    protected function _setEntityTypes(array $entityTypes)
    {
        $this->_application->getPlatform()->setOption('entity_types', $entityTypes);
    }

    private function _createEntityPropertyFieldConfig($entityType, $propertyName, array $propertyInfo)
    {
        return $this->getModel()
            ->create('FieldConfig')
            ->markNew()
            ->set('property', $propertyName)
            ->set('name', strtolower($entityType . '_' . $propertyName))
            ->set('type', $propertyInfo['type'])
            ->set('system', self::FIELD_REALM_ENTITY_TYPE_DEFAULT)
            ->set('storage', isset($propertyInfo['storage']) ? $propertyInfo['storage'] : 'sql')
            ->set('entitytype_name', $entityType)
            ->set('settings', (array)@$propertyInfo['settings']);
    }

    public function createEntityBundles(Sabai_Addon $addon, $entityType, array $bundles, $commit = true, array &$newFields = null)
    {
        if (!isset($newFields)) $newFields = array();
        $created_bundles = array();
        foreach ($bundles as $bundle_name => $bundle_info) {
            if (!$bundle = $this->_createEntityBundle($addon, $entityType, $bundle_name, $bundle_info, $newFields)) {
                unset($bundles[$bundle_name]);
                continue;
            }
            $created_bundles[$bundle_name] = $bundle;
        }
        
        if (empty($created_bundles)) return array();
        
        // Update fields
        if (!empty($newFields)) $this->createFieldStorage($newFields);
        
        if (!empty($created_bundles)) {
            $this->_application->Action('entity_create_bundles_success', array($entityType, $created_bundles));
        }
        
        if ($commit) $this->getModel()->commit();
        
        // Remove cache from Entity_Bundle helper
        foreach (array_keys($created_bundles) as $bundle_name) {
            unset(Sabai_Addon_Entity_Helper_Bundle::$bundles[$bundle_name]);
        }

        return $created_bundles;
    }

    public function updateEntityBundles(Sabai_Addon $addon, $entityType, array $bundles, $commit = true, array &$newFields = null, array &$removedFields = null, array &$updatedFields = null)
    {
        if (!isset($newFields)) $newFields = array();
        if (!isset($removedFields)) $removedFields = array();
        if (!isset($updatedFields)) $updatedFields = array();
        $current_bundles = $new_bundles = $deleted_bundles = array();
        foreach ($this->getModel('Bundle')->entitytypeName_is($entityType)->addon_is($addon->getName())->fetch() as $current_bundle) {
            if (isset($bundles[$current_bundle->name])) {
                $this->_updateEntityBundle($current_bundle, $bundles[$current_bundle->name], $newFields, $removedFields, $updatedFields);
                $current_bundles[$current_bundle->name] = $current_bundle;
            } else {
                $this->_deleteEntityBundle($current_bundle, $removedFields);
                $deleted_bundles[$current_bundle->name] = $current_bundle;
            }
        }
        foreach (array_diff(array_keys($bundles), array_keys($current_bundles)) as $name) {
            $bundle = $this->_createEntityBundle($addon, $entityType, $name, $bundles[$name], $newFields);
            $new_bundles[$bundle->name] = $bundle;
        }

        // Update fields
        if (!empty($newFields)) {
            $this->createFieldStorage($newFields);
        }
        if (!empty($removedFields)) {
            $this->deleteFieldStorage($removedFields);
            $this->_application->Action('entity_delete_field_configs_success', array($removedFields));
        }
        if (!empty($updatedFields)) {
            $this->updateFieldStorage($updatedFields);
        }
        
        if (!empty($current_bundles)) {
            $this->_application->Action('entity_update_bundles_success', array($entityType, $current_bundles));
        }
        if (!empty($deleted_bundles)) {
            $this->_application->Action('entity_delete_bundles_success', array($entityType, $deleted_bundles));
        }
        if (!empty($new_bundles)) {
            $this->_application->Action('entity_create_bundles_success', array($entityType, $new_bundles));
        }
        
        if ($commit) $this->getModel()->commit();
        
        // Remove cache from Entity_Bundle helper
        foreach (array_keys($current_bundles) as $bundle_name) {
            unset(Sabai_Addon_Entity_Helper_Bundle::$bundles[$bundle_name]);
        }

        return $current_bundles + $new_bundles;
    }

    public function deleteEntityBundles(Sabai_Addon $addon, $entityType, array $bundles = null, array &$removedFields = null)
    {
        if (!isset($removedFields)) $removedFields = array();
        if (!isset($bundles)) {
            $bundles = $this->getModel('Bundle')
                ->entitytypeName_is($entityType)
                ->addon_is($addon->getName())
                ->fetch()
                ->with('Fields', 'FieldConfig');
        }
        $deleted_bundles = $field_names = array();
        foreach ($bundles as $bundle) {
            $this->_deleteEntityBundle($bundle, $removedFields);
            $deleted_bundles[$bundle->name] = $bundle;
            // collect field names
            foreach ($bundle->Fields as $field) {
                $field_names[$field->getFieldName()] = $field->getFieldName();
            }
        }

        // Update fields
        if (!empty($removedFields)) {
            $this->deleteFieldStorage($removedFields);
            $this->_application->Action('entity_delete_field_configs_success', array($removedFields));
        }
        
        // Delete field data of deleted bundles
        $field_names = array_diff_key($field_names, $removedFields); // exclude removed fields
        $this->_application->Entity_FieldStorageImpl('sql')
            ->entityFieldStoragePurgeValuesByBundle(array_keys($deleted_bundles), $field_names);

        if (!empty($deleted_bundles)) {
            $this->_application->Action('entity_delete_bundles_success', array($entityType, $deleted_bundles));
        }

        return $deleted_bundles;
    }

    private function _createEntityBundle(Sabai_Addon $addon, $entityType, $name, array $info, array &$newFields)
    {
        // Bundle name must start with an alphabet followed by optional alphanumeric characters
        if (!preg_match('/^[a-z][a-z0-9_]*[a-z0-9]$/', $name)) return;

        // Some names are reserved
        if (in_array($name, self::$_reservedBundleNames)
            || strcasecmp($name, $entityType) === 0
        ) {
            return;
        }

        // Get the model
        $model = $this->getModel();

        // Make sure content bundle with the same name does not exist
        if ($model->Bundle->entitytypeName_is($entityType)->name_is($name)->count()) return;

        $_info = array_diff_key($info, array_flip(array('label', 'label_singular', 'system', 'fields', 'properties')));
        $bundle = $model->create('Bundle')
            ->markNew()
            ->set('entitytype_name', $entityType)
            ->set('system', isset($info['system']) ? !empty($info['system']) : true)
            ->set('name', $name)
            ->set('type', $info['type'])
            ->set('path', isset($info['path']) && strlen($info['path']) ? $info['path'] : '/' . str_replace('_', '/', $name))
            ->set('addon', $addon->getName())
            ->set('label', $this->_application->Filter('entity_type_label', $info['label'], array($entityType, $name)))
            ->set('label_singular', $this->_application->Filter('entity_type_label_singular', isset($info['label_singular']) ? $info['label_singular'] : $info['label'], array($entityType, $name)))
            ->set('info', $_info);

        // Create entity type property fields
        $this->_assignEntityPropertyFields($bundle, $info);

        // Add extra fields associated with the bundle if any
        foreach ((array)@$info['fields'] as $field_name => $field_info) {
            if ($field = $this->_createEntityField($bundle, $field_name, $field_info, self::FIELD_REALM_BUNDLE_DEFAULT)) {
                if (!$field->FieldConfig->id) {
                    $newFields[$field->getFieldName()] = $field->FieldConfig;
                }
            }
        }
      
        $this->_application->Action('entity_create_bundle', array($bundle, $info, &$newFields));
        
        $this->getModel()->commit();

        return $bundle->reload();
    }
        
    public function createEntityField(Sabai_Addon_Entity_Model_Bundle $bundle, $fieldName, array $fieldInfo, $realm = self::FIELD_REALM_ALL, $overwrite = false)
    {
        $updatedFields = array();
        if (!$field = $this->_createEntityField($bundle, $fieldName, $fieldInfo, $realm, $overwrite, $updatedFields)) {
            return;
        }
        $is_new = $field->FieldConfig->id ? false : true;
        $field->FieldConfig->commit();
        $field->commit();

        if ($is_new) {
            $this->createFieldStorage(array($field->FieldConfig));
        } else {
            // Update field storage if schema has changed
            if (!empty($updatedFields)) {
                $this->updateFieldStorage($updatedFields);
            }
        }
        
        return $field;
    }
    
    public function createEntityPropertyField(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_Model_FieldConfig $fieldConfig, array $fieldInfo, $commit = true, $overwrite = false)
    {
        if (!$field = $this->_createEntityPropertyField($bundle, $fieldConfig, $fieldInfo, $overwrite)) {
            return;
        }
        if ($commit) {
            $this->getModel()->commit();
        }
        
        return $field;
    }
    
    private function _createEntityPropertyField(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_Model_FieldConfig $fieldConfig, array $fieldInfo, $overwrite = false)
    {
        if (!$field_type_info = $this->_isValidFieldInfo($bundle, $fieldInfo)) {
            return;
        }
        
        // Fetch field
        foreach ($fieldConfig->Fields as $_field) {
            if ($_field->bundle_id == $bundle->id) {
                $field = $_field;
            }
        }
        if (!$field) {
            // Create field
            $field = $bundle->createField()->markNew()->set('FieldConfig', $fieldConfig);
        }
        
        $fieldInfo['max_num_items'] = 1;
        return $this->_updateEntityField($field, $fieldInfo, $field_type_info, $overwrite);
    }
        
    private function _isValidFieldInfo(Sabai_Addon_Entity_Model_Bundle $bundle, array $fieldInfo)
    {
        if (!isset($fieldInfo['type'])) {
            return false;
        }

        $field_types = $this->_application->Field_Types();
        if (!$field_type_info = @$field_types[$fieldInfo['type']]) {
            // the field type does not exist
            return false;
        }
        
        if (isset($field_type_info['entity_types'])
            && !in_array($bundle->entitytype_name, $field_type_info['entity_types'])
        ) {
            // the field type does not support the entity type of the bundle
            return false;
        }
        
        return $field_type_info;
    }
    
    private function _updateEntityField(Sabai_Addon_Entity_Model_Field $field, array $fieldInfo, array $fieldTypeInfo, $overwrite)
    {
        $widget = isset($fieldInfo['widget']) ? $fieldInfo['widget'] : $fieldTypeInfo['default_widget'];
        if ($widget
            && !isset($fieldInfo['max_num_items'])
            && !$this->_application->Field_WidgetImpl($widget)->fieldWidgetGetInfo('accept_multiple')
        ) {
            $fieldInfo['max_num_items'] = 1;
        }
        $renderer = isset($fieldInfo['renderer']) ? $fieldInfo['renderer'] : $fieldTypeInfo['default_renderer'];
        // Set custom field data
        if (!empty($fieldInfo['data'])) {
            foreach ($fieldInfo['data'] as $data_k => $data_v) {
                $field->setFieldData($data_k, $data_v);
            }
        }
        // Set default field data
        if (!$field->id // newfield
            || $overwrite // overwrite?
            || !$this->_application->isAddonLoaded('FieldUI') // FieldUI add-on is not installed, meaning the field has not yet been customized
        ) {
            $views = isset($fieldInfo['view']) ? $fieldInfo['view'] : array();
            if (!empty($fieldInfo['renderer_settings'])) {
                foreach (array_keys($fieldInfo['renderer_settings']) as $view) {
                    $views[$view] = $view;
                }
            }
            $field->setFieldData('title', @$fieldInfo['title'])
                ->setFieldData('title_type', @$fieldInfo['title_type'])
                ->setFieldData('label', isset($fieldInfo['label']) ? $fieldInfo['label'] : $fieldTypeInfo['label'])
                ->setFieldData('hide_label', !empty($fieldInfo['hide_label']))
                ->setFieldData('description', (string)@$fieldInfo['description'])
                ->setFieldWeight((int)@$fieldInfo['weight'])
                ->setFieldMaxNumItems($fieldInfo['max_num_items'])
                ->setFieldWidget($widget)
                ->setFieldWidgetSettings((array)@$fieldInfo['widget_settings'])
                ->setFieldData('renderer', $renderer)
                ->setFieldData('renderer_settings', (array)@$fieldInfo['renderer_settings'])
                ->setFieldData('default_value', @$fieldInfo['default_value'])
                ->setFieldData('view', $views);
            if (!$field->isPropertyField()) {   
                $field->setFieldData('required', !empty($fieldInfo['required']))
                    ->setFieldData('disabled', !empty($fieldInfo['disabled']));
            }
            if (isset($fieldInfo['filter'])) {
                $this->_createEntityFieldFilter($field, $fieldInfo['filter']);
            }
        } else {
            // Set field widget if there isn't any set
            if ($widget) {
                if (!$field->getFieldWidget()) {
                    $field->setFieldWidget($widget);
                }
                if ($this->_application->Field_WidgetImpl($widget)->fieldWidgetGetInfo('admin_only')) {
                    // Update title/label since they are non-editable
                    $field->setFieldData('title', @$fieldInfo['title'])
                        ->setFieldData('title_type', @$fieldInfo['title_type'])
                        ->setFieldData('label', isset($fieldInfo['label']) ? $fieldInfo['label'] : $fieldTypeInfo['label']);
                }
            } else {
                // Widget for this field no longer exists
                if ($field->getFieldWidget()) {
                    $field->setFieldWidget(null)->setFieldWidgetSettings(array());
                }
            }
            // Set field renderer if there isn't any set
            if ($renderer) {
                if (!$field->getFieldData('renderer')) {
                    $field->setFieldData('renderer', $renderer)->setFieldData('renderer_settings', (array)@$fieldInfo['renderer_settings']);
                } else {
                    $renderer_settings = (array)$field->getFieldData('renderer_settings');
                    foreach ((array)@$fieldInfo['renderer_settings'] as $view => $_renderer_settings) {
                        if (!isset($renderer_settings[$view][$_renderer])) {
                            $renderer_settings[$view][$_renderer] = $__renderer_settings;
                        } else {
                            $renderer_settings[$view][$_renderer] += $fieldInfo['renderer_settings'][$view][$_renderer];
                        }
                    }
                    $field->setFieldData('renderer_settings', $renderer_settings);
                }
            } else {
                // Renderer for this field no longer exists
                $field->setFieldData('renderer', null)->setFieldData('renderer_settings', null);
            }
            // Set field filter if there isn't any set
            if (isset($fieldInfo['filter']) && !count($field->Filters)) {
                $this->_createEntityFieldFilter($field, $fieldInfo['filter']);
            }
            // Set field view if there isn't any set
            if (isset($fieldInfo['view'])) {
                if (null === $field->getFieldView('default')) {
                    $field->setFieldData('view', $fieldInfo['view']);
                }
            }
        }
        // Always overwrite the followings if property field
        if ($field->isPropertyField()) {     
            if (isset($fieldInfo['required'])) {
                $field->setFieldData('required', !empty($fieldInfo['required']));
            }
            if (isset($fieldInfo['disabled'])) {
                $field->setFieldData('disabled', !empty($fieldInfo['disabled']));
            }
        }
        
        return $field;
    }
    
    private function _createEntityFieldFilter(Sabai_Addon_Entity_Model_Field $field, $filterInfo)
    {
        if (!isset($filterInfo['type']) || !isset($filterInfo['name'])) return;
        $filter = $field->createFilter()->markNew();
        $filter->type = $filterInfo['type'];
        $filter->name = $filterInfo['name'];
        $filter->bundle_id = $field->bundle_id;
        $filter->data = array(
            'weight' => (int)@$filterInfo['weight'],
            'row' => isset($filterInfo['row']) ? (int)$filterInfo['row'] : 1,
            'column' => isset($filterInfo['col']) ? (int)$filterInfo['col'] : 1,
            'label' => (string)@$filterInfo['title'],
            'admin_title' => strval(isset($filterInfo['admin_title']) ? $filterInfo['admin_title'] : @$filterInfo['title']),
            'description' => (string)@$filterInfo['description'],
            'settings' => (array)@$filterInfo['settings'],
            'disabled' => !empty($filterInfo['disabled']),
        );
        return $filter;
    }

    private function _createEntityField(Sabai_Addon_Entity_Model_Bundle $bundle, $fieldName, array $fieldInfo, $realm = self::FIELD_REALM_ALL, $overwrite = false, array &$updatedFields = array())
    {
        if (!$field_type_info = $this->_isValidFieldInfo($bundle, $fieldInfo)) {
            return;
        }

        $field_settings = isset($fieldInfo['settings']) ? $fieldInfo['settings'] : array();
        $field_schema = (array)$this->_application->Field_TypeImpl($fieldInfo['type'])->fieldTypeGetSchema($field_settings);
        if (!is_object($fieldName)) {
            $fieldName = strtolower(trim($fieldName));
            if (strlen($fieldName) === 0) return;

            if (!$field_config = $this->getModel('FieldConfig')->name_is($fieldName)->fetchOne()) {
                $field_config = $this->getModel()
                    ->create('FieldConfig')
                    ->markNew()
                    ->set('name', $fieldName)
                    ->set('system', $realm)
                    ->set('storage', isset($fieldInfo['storage']) ? $fieldInfo['storage'] : 'sql')
                    ->set('settings', $field_settings);
                if ($realm !== self::FIELD_REALM_ALL) {
                    $field_config->set('Bundle', $bundle)->set('entitytype_name', $bundle->entitytype_name);
                }
            } else {
                $is_update = true;
            }
        } else {
            $is_update = true;
            $field_config = $fieldName;
        }
        if (!empty($is_update)) {
            if ($overwrite) {
                $field_config->settings = $field_settings;
            } else {
                $field_config->settings += $field_settings;
            }
            //if ($field_config->schema !== $field_schema) {
                // Notify that field schema has changed
                $updatedFields[$field_config->storage]['old'][$field_config->name] = $field_config->schema;
                $updatedFields[$field_config->storage]['new'][$field_config->name] = $field_schema;
            //}
            foreach ($field_config->Fields as $_field) {
                if ($_field->bundle_id == $bundle->id) {
                    $field = $_field;
                }
            }
        }
        if (!isset($field)) {
            $field = $bundle->createField()->markNew();
        }
        $field->FieldConfig = $field_config;
        $field_config->schema = $field_schema;
        $field_config->type = $fieldInfo['type'];
        
        return $this->_updateEntityField($field, $fieldInfo, $field_type_info, $overwrite);
    }
    
    private function _assignEntityPropertyFields(Sabai_Addon_Entity_Model_Bundle $bundle, array $info)
    {
        $property_fields = $this->getModel('FieldConfig')->entitytypeName_is($bundle->entitytype_name)->bundleId_is(0)->fetch()->with('Fields');
        if (count($property_fields)) {
            $entity_type_info = $this->_application->Entity_TypeImpl($bundle->entitytype_name, false)->entityTypeGetInfo();
            foreach ($property_fields as $property_field) {
                if (!isset($entity_type_info['properties'][$property_field->property])) {
                    // Delete stale data
                    $property_field->markRemoved()->commit();
                    continue;
                }
                $property_field_settings = $entity_type_info['properties'][$property_field->property];
                // Each bunde can set custom field settings but not overwrite the default
                if (!empty($info['properties'][$property_field->property])) {
                    $property_field_settings += $info['properties'][$property_field->property];
                    // Except for the label which can be overwritten
                    if (isset($info['properties'][$property_field->property]['label'])) {
                        $property_field_settings['label'] = $info['properties'][$property_field->property]['label'];
                    }
                }
                $this->_createEntityPropertyField($bundle, $property_field, $property_field_settings);
            }
        }
    }

    private function _updateEntityBundle(Sabai_Addon_Entity_Model_Bundle $bundle, array $info, array &$newFields, array &$removedFields, array &$updatedFields)
    {
        $_info = array_diff_key($info, array_flip(array('label', 'label_singular', 'system', 'fields', 'properties', 'path', 'type')));
        $bundle->set('label', $this->_application->Filter('entity_type_label', $info['label'], array($bundle->entitytype_name, $bundle->name)))
            ->set('label_singular', $this->_application->Filter('entity_type_label_singular', isset($info['label_singular']) ? $info['label_singular'] : $info['label'], array($bundle->entitytype_name, $bundle->name)))
            ->set('info', $_info);
        // Has path/type been changed?
        if ($bundle->getPath() != $info['path']) {
            $bundle->set('path', $info['path']);
        }
        // Update type, for backward compat
        $bundle->set('type', $info['type']);
        // Update entity type property fields
        $this->_assignEntityPropertyFields($bundle, $info);
        // Update bundle specific fields
        $current_fields = $this->getModel('FieldConfig')
            ->system_is(self::FIELD_REALM_BUNDLE_DEFAULT)
            ->bundleId_is($bundle->id)
            ->fetch()
            ->with('Fields');
        if (!empty($info['fields'])) {
            $fields_already_installed = array();
            foreach ($current_fields as $current_field) {
                if (!isset($info['fields'][$current_field->name])) {
                    $current_field->markRemoved();
                    $removedFields[$current_field->name] = $current_field;

                    continue;
                }
                $this->_createEntityField($bundle, $current_field, $info['fields'][$current_field->name], self::FIELD_REALM_ALL, false, $updatedFields);
                $fields_already_installed[] = $current_field->name;
            }
            // Create newly added fields
            foreach (array_diff(array_keys($info['fields']), $fields_already_installed) as $field_name) {
                if ($field = $this->_createEntityField($bundle, $field_name, $info['fields'][$field_name], self::FIELD_REALM_BUNDLE_DEFAULT)) {
                    if (!$field->FieldConfig->id) {
                        $newFields[$field->FieldConfig->name] = $field->FieldConfig;
                    }
                }
            }
        } else {
            foreach ($current_fields as $current_field) {
                $current_field->markRemoved();
                $removedFields[$current_field->name] = $current_field;
            }
        }        
        
        $this->_application->Action('entity_update_bundle', array($bundle, $info, &$newFields, &$removedFields));

        $this->getModel()->commit();
        
        return $bundle->reload();
    }

    private function _deleteEntityBundle($bundle, array &$removedFields)
    {
        $bundle->markRemoved();

        $fields = $this->getModel('FieldConfig')
            ->bundleId_is($bundle->id)
            ->fetch()
            ->with('Fields');
        foreach ($fields as $field) {
            $field->markRemoved();
            $removedFields[$field->name] = $field;
        }
        
        $this->_application->Action('entity_delete_bundle', array($bundle, &$removedFields));
        
        $this->getModel()->commit();

        return $bundle;
    }

    /**
     * Delete entities
     * @param type $entityType
     * @param array $entities An array of Sabai_Addon_Entity_IEntity objects indexed by entity IDs
     */
    public function deleteEntities($entityType, array $entities, array $extraArgs = array())
    {
        if (empty($entities)) return;
        
        // Load field values from storage so that all field values can be accessed by other addons upon delete event
        $this->_application->Entity_LoadFields($entityType, $entities, null, true, false);
        
        if (empty($extraArgs['fields_only'])) {
            $this->_application->Entity_TypeImpl($entityType)->entityTypeDeleteEntities($entities);
        }

        // Delete fields
        $entities_by_bundle = array();
        foreach ($entities as $entity) {
            $entities_by_bundle[$entity->getBundleName()][$entity->getId()] = $entity;
        }
        $bundles = $this->_application->Entity_BundleCollection(array_keys($entities_by_bundle))->with('Fields', 'FieldConfig');
        $fields_by_storage = $bundles_arr = array();
        foreach ($bundles as $bundle) {
            foreach ($bundle->Fields as $field) {
                $fields_by_storage[$field->getFieldStorage()][$bundle->name][$field->getFieldName()] = $field->getFieldType();
            }
            $bundles_arr[$bundle->name] = $bundle;
        }
        foreach ($fields_by_storage as $field_storage => $bundle_fields) {
            foreach ($bundle_fields as $bundle_name => $fields) {
                $this->_application->Entity_FieldStorageImpl($field_storage)
                    ->entityFieldStoragePurgeValues($entityType, array_keys($entities_by_bundle[$bundle_name]), array_keys($fields));
            }
        }

        // Clear cached entity fields
        $this->_application->Entity_FieldCacheImpl()->entityFieldCacheRemove($entityType, array_keys($entities));

        // Notify entities have been deleted
        foreach ($entities as $entity) {
            $bundle = $bundles_arr[$entity->getBundleName()];
            $this->_invokeEntityEvents($entityType, $bundle->type, array($bundle, $entity, array_keys($entities), $extraArgs), 'delete', 'success');
        }
        
        foreach ($entities_by_bundle as $bundle_name => $entities) {
            $bundle = $bundles_arr[$bundle_name];
            $this->_invokeEntityEvents($entityType, $bundle->type, array($bundle, $entities, $extraArgs), 'bulk_delete', 'success');
        }
    }
    
    private function _invokeEntityEvents($entityType, $bundleType, array $params, $prefix, $suffix = '')
    {
        $prefix = 'entity_' . $prefix;
        $suffix = $suffix !== '' ? 'entity_' . $suffix : 'entity';
        $this->_application->Action($prefix . '_' . $suffix, $params);
        $this->_application->Action($prefix . '_' . $entityType . '_' . $suffix, $params);
        $this->_application->Action($prefix . '_' . $entityType . '_' . $bundleType . '_' . $suffix, $params);
    }

    public function fetchEntities($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery, $limit = 0, $offset = 0, $loadEntityFields = true)
    {
        // It is not possible to fetch entities from multiple field storages
        $field_storage = $this->_config['field_storage'];

        $entities = $this->_application->Entity_FieldStorageImpl($field_storage)
            ->entityFieldStorageQuery($entityType, $fieldQuery, $limit, $offset);
        if (empty($entities)) {
            return array();
        }
        
        foreach ($this->_application->Entity_TypeImpl($entityType)->entityTypeGetEntitiesByIds(array_keys($entities)) as $entity) {
            if (is_array($entities[$entity->getId()])) {
                // Set extra fields queried as entity data
                $entity->data = $entities[$entity->getId()];
            }
            $entities[$entity->getId()] = $entity;
        }
        if ($loadEntityFields) {
            $this->_application->Entity_LoadFields($entityType, $entities, $field_storage);
        }
        return $entities;
    }

    public function countEntities($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery, $limit = 0, $offset = 0)
    {
        // It is not possible to fetch entities from multiple field storages
        $field_storage = $this->getConfig('field_storage');

        return $this->_application->Entity_FieldStorageImpl($field_storage)
            ->entityFieldStorageQueryCount($entityType, $fieldQuery, $limit, $offset);
    }

    public function paginateEntities($entityType, Sabai_Addon_Entity_FieldQuery $fieldQuery, $limit = 20, $loadEntityFields = true)
    {
        return new SabaiFramework_Paginator_Custom(
            array($this, 'countEntities'),
            array($this, 'fetchEntities'),
            $limit,
            array($loadEntityFields),
            array($entityType, $fieldQuery),
            array()
        );
    }

    public function createFieldStorage(array $fieldConfigs)
    {
        $fields = array();
        // Fetch new schema for each field
        foreach ($fieldConfigs as $field_config) {
            $fields[$field_config->storage][$field_config->name] = $field_config->schema;
        }                                               
        // Create storage
        foreach ($fields as $storage => $_fields) {
            $this->_application->Entity_FieldStorageImpl($storage)->entityFieldStorageCreate($_fields);
        }
        $this->_application->getPlatform()->deleteCache('entity_field_column_types');
    }
    
    public function deleteFieldStorage(array $fieldConfigs)
    {
        $fields = array();
        // Fetch current schema for each field
        foreach ($fieldConfigs as $field_config) {
            $fields[$field_config->storage][$field_config->name] = $field_config->schema;
        }                                         
        // Delete storage
        foreach ($fields as $storage => $_fields) {
            $this->_application->Entity_FieldStorageImpl($storage)->entityFieldStorageDelete($_fields);
        }
        $this->_application->getPlatform()->deleteCache('entity_field_column_types');
    }
    
    public function updateFieldStorage(array $updatedFields)
    {
        foreach ($updatedFields as $field_storage => $field_schema) {
            $this->_application->Entity_FieldStorageImpl($field_storage)->entityFieldStorageUpdate($field_schema['new'], $field_schema['old']);
        }
        $this->_application->getPlatform()->deleteCache('entity_field_column_types');
    }

    public function onEntityIFieldStoragesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('entity_fieldstorages');
    }

    public function onEntityIFieldStoragesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('entity_fieldstorages');
    }

    public function onEntityIFieldStoragesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('entity_fieldstorages');
    }

    public function getDefaultConfig()
    {
        return array(
            'field_storage' => 'sql',
        );
    }

    /* Start implementation of Sabai_Addon_Entity_IFieldCache */

    public function entityFieldCacheSave($entityType, array $entities)
    {
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache')->entitytypeName_is($entityType)->entityId_in(array_keys($entities));
        $model->getGateway('FieldCache')->deleteByCriteria($criteria);
        foreach ($entities as $entity_id => $entity) {
            $fieldcache = $model->create('FieldCache');
            $fieldcache->entity_id = $entity_id;
            $fieldcache->entitytype_name = $entityType;
            $fieldcache->fields = array($entity->getFieldValues(), $entity->getFieldTypes());
            $fieldcache->bundle_id = $this->_application->Entity_Bundle($entity)->id;
            $fieldcache->markNew();
        }
        $model->commit();
    }

    public function entityFieldCacheLoad($entityType, array $entities)
    {
        $ret = array();
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache')->entitytypeName_is($entityType)->entityId_in(array_keys($entities));
        $rs = $model->getGateway('FieldCache')->selectByCriteria($criteria, array('fieldcache_entity_id', 'fieldcache_fields'));
        while ($row = $rs->fetchRow()) {
            $fields = unserialize($row[1]);
            if (!isset($fields[1])) {
                continue; // older version don't have field types cached so skip it to force reload cache
            }
            $entities[$row[0]]->initFields($fields[0], $fields[1]);
            $ret[] = $row[0];
        }
        return $ret;
    }

    public function entityFieldCacheRemove($entityType, array $entityIds)
    {
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache')->entitytypeName_is($entityType)->entityId_in($entityIds);
        $model->getGateway('FieldCache')->deleteByCriteria($criteria);
    }

    public function entityFieldCacheClean($entityType = null)
    {
        $model = $this->getModel();
        $criteria = $model->createCriteria('FieldCache');
        if (isset($entityType)) {
            $criteria->entitytypeName_is($entityType);
        }
        $model->getGateway('FieldCache')->deleteByCriteria($criteria);
    }

    /* End implementation of Sabai_Addon_Entity_IFieldCache */

    public function onFieldTypeDeleted($fieldType)
    {
        $field_configs = array();
        foreach ($this->getModel('FieldConfig')->type_is($fieldType->name)->fetch()->with('Fields') as $field_config) {
            $field_config->markRemoved();
            $field_configs[$field_config->name] = $field_config;
        }
        $this->getModel()->commit();
        $this->deleteFieldStorage($field_configs);
        $this->_application->Action('entity_delete_field_configs_success', array($field_configs));
    }
    
    public function uninstall(ArrayObject $log)
    {
        // Remove tables created by custom fields
        
        $fields = array();
        foreach ($this->getModel('FieldConfig')->fetch() as $field) {
            if ($field->property) continue;
            
            $fields[] = $field;
        }

        // Remove field tables
        if (!empty($fields)) {
            $this->deleteFieldStorage($fields);
        }
        
        parent::uninstall($log);
    }
    
    public function onSystemClearCache()
    {
        $this->_application->Entity_FieldCacheImpl()->entityFieldCacheClean();
    }
    
    public function createEntityFilter(Sabai_Addon_Entity_Model_Field $field, $type, $name, array $data)
    {
        $field_types = $this->_application->FieldUI_FilterableFieldTypes($field->Bundle);
        if (!isset($field_types[$field->getFieldType()])
            || !isset($field_types[$field->getFieldType()]['filters'][$type])
        ) {
            return;
        }

        if (!$filter = $this->getModel('Filter')->fieldId_is($field->getFieldId())->name_is($name)->fetchOne()) {
            $filter = $this->_createEntityFieldFilter($field, array('type' => $type, 'name' => $name));
        } else {
            $filter->type = $type;
        }
        $data += $filter->data;
        unset($data['title']); // old version had this
        $filter->data = $data;
        return $filter->commit();
    }
}
