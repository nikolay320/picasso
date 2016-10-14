<?php
class Sabai_Addon_Entity_Model_FieldsWithFieldConfig extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('fieldconfig_id', 'FieldConfig', $collection);
    }
}