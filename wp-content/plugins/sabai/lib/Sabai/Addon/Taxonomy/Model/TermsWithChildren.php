<?php
class Sabai_Addon_Taxonomy_Model_TermsWithChildren extends SabaiFramework_Model_EntityCollection_Decorator_ChildEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('Term', 'term_parent', $collection);
    }
}