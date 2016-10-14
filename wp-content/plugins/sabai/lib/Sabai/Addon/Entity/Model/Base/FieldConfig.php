<?php
/* This file has been auto-generated. Do not edit this file directly. */

abstract class Sabai_Addon_Entity_Model_Base_FieldConfig extends SabaiFramework_Model_Entity
{
    public function __construct(SabaiFramework_Model $model)
    {
        parent::__construct('FieldConfig', $model);
        $this->_vars = array('fieldconfig_name' => null, 'fieldconfig_type' => null, 'fieldconfig_storage' => null, 'fieldconfig_system' => 0, 'fieldconfig_settings' => null, 'fieldconfig_property' => null, 'fieldconfig_schema' => null, 'fieldconfig_entitytype_name' => null, 'fieldconfig_id' => 0, 'fieldconfig_created' => 0, 'fieldconfig_updated' => 0, 'fieldconfig_bundle_id' => 0);
    }

    public function __clone()
    {
        $this->_vars = array('fieldconfig_id' => 0, 'fieldconfig_created' => 0, 'fieldconfig_updated' => 0) + $this->_vars;
    }

    public function __toString()
    {
        return $this->__get('name');
    }

    public function addField(Sabai_Addon_Entity_Model_Field $entity)
    {
        $entity->FieldConfig = $this;

        return $this;
    }

    public function removeField(Sabai_Addon_Entity_Model_Field $entity)
    {
        $this->removeFieldById($entity->id);

        return $this;
    }

    public function removeFieldById($id)
    {
        $this->_removeEntityById('field_id', 'Field', $id);

        return $this;
    }

    public function createField()
    {
        return $this->_createEntity('Field');
    }

    public function removeFields()
    {
        $this->_removeEntities('Field');

        return $this;
    }

    public function __get($name)
    {
        if ($name === 'name')
            return $this->_vars['fieldconfig_name'];
        elseif ($name === 'type')
            return $this->_vars['fieldconfig_type'];
        elseif ($name === 'storage')
            return $this->_vars['fieldconfig_storage'];
        elseif ($name === 'system')
            return $this->_vars['fieldconfig_system'];
        elseif ($name === 'settings')
            return $this->_vars['fieldconfig_settings'];
        elseif ($name === 'property')
            return $this->_vars['fieldconfig_property'];
        elseif ($name === 'schema')
            return $this->_vars['fieldconfig_schema'];
        elseif ($name === 'entitytype_name')
            return $this->_vars['fieldconfig_entitytype_name'];
        elseif ($name === 'id')
            return $this->_vars['fieldconfig_id'];
        elseif ($name === 'created')
            return $this->_vars['fieldconfig_created'];
        elseif ($name === 'updated')
            return $this->_vars['fieldconfig_updated'];
        elseif ($name === 'bundle_id')
            return $this->_vars['fieldconfig_bundle_id'];
        elseif ($name === 'Bundle')
            return $this->_fetchEntity('Bundle', 'bundle_id');
        elseif ($name === 'Fields')
            return $this->_fetchEntities('Field', 'Fields');
        else
            return $this->fetchObject($name);
    }

    public function __set($name, $value)
    {
        if ($name === 'name')
            $this->_setVar('fieldconfig_name', $value);
        elseif ($name === 'type')
            $this->_setVar('fieldconfig_type', $value);
        elseif ($name === 'storage')
            $this->_setVar('fieldconfig_storage', $value);
        elseif ($name === 'system')
            $this->_setVar('fieldconfig_system', $value);
        elseif ($name === 'settings')
            $this->_setVar('fieldconfig_settings', $value);
        elseif ($name === 'property')
            $this->_setVar('fieldconfig_property', $value);
        elseif ($name === 'schema')
            $this->_setVar('fieldconfig_schema', $value);
        elseif ($name === 'entitytype_name')
            $this->_setVar('fieldconfig_entitytype_name', $value);
        elseif ($name === 'id')
            $this->_setVar('fieldconfig_id', $value);
        elseif ($name === 'bundle_id')
            $this->_assignEntityById('Bundle', $value, 'fieldconfig_bundle_id');
        elseif ($name === 'Bundle') {
            $_value = is_array($value) ? $value[0] : $value;
            if (is_object($_value)) {
                $this->_assignEntity($_value, 'fieldconfig_bundle_id');
            } else {
                $this->_assignEntityById('Bundle', $_value, 'fieldconfig_bundle_id');
            }
        }
        elseif ($name === 'Fields') {
            $this->removeFields();
            foreach (array_keys($value) as $i) $this->addField($value[$i]);
        }
        else
            $this->assignObject($name, $value);
    }

    protected function _initVar($name, $value)
    {
        if ($name === 'fieldconfig_system')
            $this->_vars['fieldconfig_system'] = (int)$value;
        elseif ($name === 'fieldconfig_settings')
            $this->_vars['fieldconfig_settings'] = @unserialize($value);
        elseif ($name === 'fieldconfig_schema')
            $this->_vars['fieldconfig_schema'] = @unserialize($value);
        elseif ($name === 'fieldconfig_id')
            $this->_vars['fieldconfig_id'] = (int)$value;
        elseif ($name === 'fieldconfig_created')
            $this->_vars['fieldconfig_created'] = (int)$value;
        elseif ($name === 'fieldconfig_updated')
            $this->_vars['fieldconfig_updated'] = (int)$value;
        elseif ($name === 'fieldconfig_bundle_id')
            $this->_vars['fieldconfig_bundle_id'] = (int)$value;
        else
            $this->_vars[$name] = $value;
    }
}

abstract class Sabai_Addon_Entity_Model_Base_FieldConfigRepository extends SabaiFramework_Model_EntityRepository
{
    public function __construct(SabaiFramework_Model $model)
    {
        parent::__construct('FieldConfig', $model);
    }

    public function fetchByBundle($id, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        return $this->_fetchByForeign('fieldconfig_bundle_id', $id, $limit, $offset, $sort, $order);
    }

    public function paginateByBundle($id, $perpage = 10, $sort = null, $order = null)
    {
        return $this->_paginateByEntity('Bundle', $id, $perpage, $sort, $order);
    }

    public function countByBundle($id)
    {
        return $this->_countByForeign('fieldconfig_bundle_id', $id);
    }

    public function fetchByBundleAndCriteria($id, SabaiFramework_Criteria $criteria, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        return $this->_fetchByForeignAndCriteria('fieldconfig_bundle_id', $id, $criteria, $limit, $offset, $sort, $order);
    }

    public function paginateByBundleAndCriteria($id, SabaiFramework_Criteria $criteria, $perpage = 10, $sort = null, $order = null)
    {
        return $this->_paginateByEntityAndCriteria('Bundle', $id, $criteria, $perpage, $sort, $order);
    }

    public function countByBundleAndCriteria($id, SabaiFramework_Criteria $criteria)
    {
        return $this->_countByForeignAndCriteria('fieldconfig_bundle_id', $id, $criteria);
    }

    protected function _getCollectionByRowset(SabaiFramework_DB_Rowset $rs)
    {
        return new Sabai_Addon_Entity_Model_Base_FieldConfigsByRowset($rs, $this->_model->create('FieldConfig'), $this->_model);
    }

    public function createCollection(array $entities = array())
    {
        return new Sabai_Addon_Entity_Model_Base_FieldConfigs($this->_model, $entities);
    }
}

class Sabai_Addon_Entity_Model_Base_FieldConfigsByRowset extends SabaiFramework_Model_EntityCollection_Rowset
{
    public function __construct(SabaiFramework_DB_Rowset $rs, Sabai_Addon_Entity_Model_FieldConfig $emptyEntity, SabaiFramework_Model $model)
    {
        parent::__construct('FieldConfigs', $rs, $emptyEntity, $model);
    }

    protected function _loadRow(SabaiFramework_Model_Entity $entity, array $row)
    {
        $entity->initVars($row);
    }
}

class Sabai_Addon_Entity_Model_Base_FieldConfigs extends SabaiFramework_Model_EntityCollection_Array
{
    public function __construct(SabaiFramework_Model $model, array $entities = array())
    {
        parent::__construct($model, 'FieldConfigs', $entities);
    }
}