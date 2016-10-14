<?php
class Sabai_Addon_Voting_Controller_VoteEntity extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$context->getRequest()->isPostMethod() || !$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        // Check request token
        if (!$this->_checkToken($context, 'voting_vote_entity', true)) return;
        
        // Cast vote
        $results = $this->Voting_CastVote($context->entity, $context->voting_tag, $context->getRequest()->get('value'));

        $context->setSuccess($this->Entity_Url($context->entity))->setSuccessAttributes($results);
    } 
}