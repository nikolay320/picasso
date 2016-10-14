<?php
class Sabai_Addon_Comment_Model_PostsWithVotes extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('vote_post_id', 'Vote', $collection, 'Votes');
    }
}