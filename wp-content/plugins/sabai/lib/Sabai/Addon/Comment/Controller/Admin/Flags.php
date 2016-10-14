<?php
class Sabai_Addon_Comment_Controller_Admin_Flags extends Sabai_Addon_Comment_Controller_Admin_Votes
{
    protected $_tag = 'flag';
    
    protected function _getHeaders()
    {
        return array(
            'author' => __('User', 'sabai'),
            'created' => __('Submitted at', 'sabai'),
            'value' => __('Spam score', 'sabai'),
            'reason' => __('Reason', 'sabai'),
        );
    }
    
    protected function _getVoteRow(Sabai_Context $context, Sabai_Addon_Comment_Model_Vote $vote)
    {
        switch ($vote->value) {
            case Sabai_Addon_Comment::VOTE_FLAG_VALUE_OFFENSIVE:
                $reason = __('Offensive', 'sabai');
                break;
            case Sabai_Addon_Comment::VOTE_FLAG_VALUE_SPAM:
                $reason = __('Spam', 'sabai');
                break;
            case Sabai_Addon_Comment::VOTE_FLAG_VALUE_OFFTOPIC:
                $reason = __('Off topic', 'sabai');
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