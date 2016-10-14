<?php
class Sabai_Addon_Entity_Model_FieldConfigsWithFields extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('field_fieldconfig_id', 'Field', $collection, 'Fields');
    }
}