<?php
class Sabai_Addon_Voting_Model_Vote extends Sabai_Addon_Voting_Model_Base_Vote
{
    public function getFlagReason()
    {
        switch ($this->value) {
            case Sabai_Addon_Voting::FLAG_VALUE_SPAM:
                return __('It is spam', 'sabai');
            case Sabai_Addon_Voting::FLAG_VALUE_OFFENSIVE:
                return __('It contains offensive language or content', 'sabai');
            case Sabai_Addon_Voting::FLAG_VALUE_OFFTOPIC:
                return __('It does not belong here', 'sabai');
            case Sabai_Addon_Voting::FLAG_VALUE_OTHER:
                return $this->comment;
        }
    }
}

class Sabai_Addon_Voting_Model_VoteRepository extends Sabai_Addon_Voting_Model_Base_VoteRepository
{
}