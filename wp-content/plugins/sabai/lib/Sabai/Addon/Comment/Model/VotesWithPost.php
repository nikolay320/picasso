<?php
class Sabai_Addon_Comment_Model_VotesWithPost extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('post_id', 'Post', $collection);
    }
}