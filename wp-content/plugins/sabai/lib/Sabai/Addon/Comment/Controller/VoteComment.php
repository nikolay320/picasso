<?php
class Sabai_Addon_Comment_Controller_VoteComment extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Must be an Ajax request
        if (!$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        // Check request token
        if (!$this->_checkToken($context, 'comment_vote_comment', true)) {
            return;
        }
        
        // Check if the current user has already voted
        $count = $this->getModel('Vote')->postId_is($context->comment->id)->userId_is($this->getUser()->id)->count();
        if ($count) {
            $context->setError(__('You have already voted for or flagged this comment', 'sabai'));
            return;
        }
        
        // Cast vote
        $vote = $context->comment->createVote()->markNew();
        $vote->user_id = $this->getUser()->id;
        $vote->tag = 'up';
        $vote->value = $context->getRequest()->asInt('value', 1, array(1, -1));
        $vote->commit();        

        // Update comment vote fields
        $results = $context->comment->updateVoteStat('up');
        $context->comment->commit();
        
        // Update featured comments for the entity with the probability percentage of 30%
        if (!$context->comment->isFeatured() && rand(1,100) <= 30) { 
            $this->getModel()->getGateway('Post')->updateFeaturedByEntity($context->entity->getId());
        }

        // Send success response
        $context->setSuccess($this->Entity_Url($context->entity))
            ->setSuccessAttributes($results)
            ->clearFlash();
    }
}