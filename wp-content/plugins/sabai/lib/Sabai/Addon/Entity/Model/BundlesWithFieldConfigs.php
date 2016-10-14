<?php
class Sabai_Addon_Entity_Model_BundlesWithFieldConfigs extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('fieldconfig_bundle_id', 'FieldConfig', $collection, 'FieldConfigs');
    }
}