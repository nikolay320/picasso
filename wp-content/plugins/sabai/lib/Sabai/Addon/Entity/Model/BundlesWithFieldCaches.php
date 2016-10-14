<?php
class Sabai_Addon_Entity_Model_BundlesWithFieldCaches extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('fieldcache_bundle_id', 'FieldCache', $collection, 'FieldCaches');
    }
}