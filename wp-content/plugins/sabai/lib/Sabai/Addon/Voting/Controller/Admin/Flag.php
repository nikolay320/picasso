<?php
class Sabai_Addon_Voting_Controller_Admin_Flag extends Sabai_Addon_Voting_Controller_Admin_Updown
{    
    protected function _getHeaders()
    {
        return array(
            'author' => __('User', 'sabai'),
            'created' => __('Date flagged', 'sabai'),
            'value' => __('Spam score', 'sabai'),
            'reason' => __('Reason', 'sabai'),
        );
    }
    
    protected function _getSortableHeaders()
    {
        return array('created', 'value');
    }
    
    protected function _getDefaultHeader()
    {
        return 'created';
    }
    
    protected function _getTimestampHeaders()
    {
        return array('created');
    }
    
    protected function _getVoteRow(Sabai_Context $context, Sabai_Addon_Voting_Model_Vote $vote)
    {
        switch ($vote->value) {
            case Sabai_Addon_Voting::FLAG_VALUE_OFFENSIVE:
                $reason = __('Offensive', 'sabai');
                break;
            case Sabai_Addon_Voting::FLAG_VALUE_SPAM:
                $reason = __('Spam', 'sabai');
                break;
            case Sabai_Addon_Voting::FLAG_VALUE_OFFTOPIC:
                $reason = __('Off topic', 'sabai');
                break;
            case Sabai_Addon_Voting::FLAG_VALUE_OTHER:
                $reason = $vote->comment;
                break;
        }
        return array(
            'created' => $this->getPlatform()->getHumanTimeDiff($vote->created),
            'author' => $this->UserIdentityLinkWithThumbnailSmall($vote->User),
            'value' => $vote->value,
            'reason' => Sabai::h($reason),
        );
    }
}