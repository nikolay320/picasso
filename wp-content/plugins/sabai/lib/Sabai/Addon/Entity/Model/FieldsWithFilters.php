<?php
class Sabai_Addon_Entity_Model_FieldsWithFilters extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('filter_field_id', 'Filter', $collection, 'Filters');
    }
}