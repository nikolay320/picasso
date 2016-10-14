<?php
class Sabai_Addon_Questions_Controller_Feed extends Sabai_Addon_Content_Controller_Feed
{    
    protected function _getItemTitle(Sabai_Context $context, Sabai_Addon_Entity_Entity $post)
    {
        if ($post->getBundleName() !== $this->getAddon()->getAnswersBundleName()) {
            // return question title
            return $post->getTitle();
        }
        if (!$question = $this->Content_ParentPost($post)) {
            // no question for some rason. this should not happen
            return $post->getTitle();
        }
        return sprintf(__('In reply to: %s', 'sabai-discuss'), $question->getTitle());
    }
    
    protected function _createQuery(Sabai_Context $context)
    {
        return parent::_createQuery($context)
            ->propertyIsIn('post_entity_bundle_name', array($this->getAddon()->getQuestionsBundleName(), $this->getAddon()->getAnswersBundleName()));
    }
        
    protected function _getDescription(Sabai_Context $context)
    {
        return sprintf(
            __('The most recent %d questions and answers', 'sabai-discuss'),
            $this->_numItems
        );
    }
}