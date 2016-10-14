<?php
class Sabai_Addon_Voting_Controller_Admin_Rating extends Sabai_Addon_Voting_Controller_Admin_Updown
{    
    protected function _getHeaders()
    {
        return array(
            'author' => __('User', 'sabai'),
            'ip' => __('IP address', 'sabai'),
            'created' => __('Date', 'sabai'),
            'value' => __('Rating', 'sabai'),
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
        return array(
            'created' => $this->getPlatform()->getHumanTimeDiff($vote->created),
            'author' => $this->UserIdentityLinkWithThumbnailSmall($vote->User),
            'value' => $vote->value,
            'ip' => $vote->ip,
        );
    }
}