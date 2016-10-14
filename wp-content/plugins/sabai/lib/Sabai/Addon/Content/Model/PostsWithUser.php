<?php
class Sabai_Addon_Content_Model_PostsWithUser extends Sabai_ModelEntityWithUser
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}