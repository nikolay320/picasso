<?php
class Sabai_Addon_Comment_Model_PostsWithUser extends Sabai_ModelEntityWithUser
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}