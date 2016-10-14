<?php
class Sabai_Addon_Voting_Controller_Admin_Favorite extends Sabai_Addon_Voting_Controller_Admin_Updown
{    
    protected function _getHeaders()
    {
        return array(
            'author' => __('User', 'sabai'),
            'created' => __('Date added', 'sabai'),
        );
    }
    
    protected function _getSortableHeaders()
    {
        return array('created');
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
        );
    }
}