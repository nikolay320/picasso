<?php
class Sabai_Addon_Taxonomy_Model_TermsWithDescendantsCount extends SabaiFramework_Model_EntityCollection_Decorator_DescendantEntitiesCount
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('Term', $collection);
    }
}