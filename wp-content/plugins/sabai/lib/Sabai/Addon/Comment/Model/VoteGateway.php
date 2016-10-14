<?php
class Sabai_Addon_Comment_Model_VoteGateway extends Sabai_Addon_Comment_Model_Base_VoteGateway
{
    public function getResults($postId, $tag = 'up')
    {
        $sql = sprintf(
            'SELECT COUNT(*) AS count, SUM(vote_value) AS sum FROM %scomment_vote WHERE vote_post_id = %d AND vote_tag = %s GROUP BY vote_post_id',
             $this->_db->getResourcePrefix(),
             $postId,
             $this->_db->escapeString($tag)
        );
        
        return $this->_db->query($sql)->fetchAssoc();
    }
    
    public function getPostsVoted(array $postIds, $userId)
    {
        if (empty($postIds)) return array();
        
        $sql = sprintf(
            'SELECT vote_post_id FROM %scomment_vote WHERE vote_post_id IN (%s) AND vote_user_id = %d',
             $this->_db->getResourcePrefix(),
             implode(',', array_map('intval', $postIds)),
             $userId
        );
        
        return $this->_db->query($sql)->fetchAllColumns(0);
    }
}