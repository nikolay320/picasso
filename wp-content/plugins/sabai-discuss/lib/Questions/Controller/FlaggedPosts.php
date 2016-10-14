<?php
class Sabai_Addon_Questions_Controller_FlaggedPosts extends Sabai_Addon_Voting_Controller_FlaggedPosts
{
    protected $_displayMode = 'flagged', $_template = 'questions_questions_list';
    
    protected function _getBundleNames(Sabai_Context $context)
    {
        return array($this->getAddon()->getQuestionsBundleName(), $this->getAddon()->getAnswersBundleName());
    }
}
