<?php
class Sabai_Addon_Entity_Model_BundlesWithFilters extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('filter_bundle_id', 'Filter', $collection, 'Filters');
    }
}