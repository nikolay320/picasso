<?php
class Sabai_Addon_Comment_Model_Post extends Sabai_Addon_Comment_Model_Base_Post
{
    public function isHidden()
    {
        return $this->status === Sabai_Addon_Comment::POST_STATUS_HIDDEN;
    }
    
    public function isFeatured()
    {
        return $this->status === Sabai_Addon_Comment::POST_STATUS_FEATURED;
    }
       
    public function isOwnedBy($identity)
    {
        return $this->user_id === $identity->id;
    }
    
    public function isSpam()
    {
        return $this->flag_sum >= Sabai_Addon_Comment::VOTE_FLAG_VALUE_OFFENSIVE * 2 + $this->vote_sum / 3;
    }
    
    public function updateVoteStat($tag = 'up')
    {
        $results = $this->_model->getGateway('Vote')->getResults($this->id, $tag);
        if ($tag === 'up') {
            $count_var = 'vote_count';
            $sum_var = 'vote_sum';
        } else {
            $count_var = 'flag_count';
            $sum_var = 'flag_sum';
        }
        $this->$sum_var = $results['sum'];
        $this->$count_var = $results['count'];
        
        return $results;
    }
    
    public function toArray()
    {
        return array(
            'id' => $this->id,
            'published_at' => $this->published_at,
            'flag_count' => $this->flag_count,
            'flag_sum' => $this->flag_sum,
            'vote_count' => $this->vote_count,
            'vote_sum' => $this->vote_sum,
            'flag_disabled' => $this->flag_disabled,
            'vote_disabled' => $this->vote_disabled,
            'edit_count' => $this->edit_count,
            'edit_last_at' => $this->edit_last_at,
            'is_hidden' => $this->isHidden(),
            'author' => $this->User,
            'body' => $this->body_html,
        );
    }
}

class Sabai_Addon_Comment_Model_PostRepository extends Sabai_Addon_Comment_Model_Base_PostRepository
{
    public function getFeaturedByEntities(array $entityIds)
    {
        return $this->_getCollection($this->_model->getGateway($this->getName())->getFeaturedByEntities($entityIds));
    }
}