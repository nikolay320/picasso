<?php
class Sabai_Addon_Voting_Model_VotesWithUser extends Sabai_ModelEntityWithUser
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}