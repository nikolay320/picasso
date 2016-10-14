<?php
class Sabai_Addon_Content_Model_PostsWithEntityBundle extends Sabai_Addon_Entity_Model_WithBundle
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}