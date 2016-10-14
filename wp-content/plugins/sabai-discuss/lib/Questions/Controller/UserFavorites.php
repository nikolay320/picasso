<?php
class Sabai_Addon_Questions_Controller_UserFavorites extends Sabai_Addon_Content_Controller_FavoritePosts
{
    protected $_template = 'questions_favorites';
    
    protected function _getBundleNames(Sabai_Context $context)
    {
        return array($this->getAddon()->getQuestionsBundleName(), $this->getAddon()->getAnswersBundleName());
    }
}