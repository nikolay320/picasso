<?php
class Sabai_Addon_Comment_Controller_Comments extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if ($this->HasPermission($context->entity->getBundleName() . '_manage')) {
            $comments = $this->getModel('Post')
                ->entityId_is($context->entity->getId())
                ->fetch(0, 0, array('published_at'), array('ASC'));
        } else {
            $comments = $this->getModel('Post')
                ->entityId_is($context->entity->getId())
                ->status_isNot(Sabai_Addon_Comment::POST_STATUS_HIDDEN)
                ->fetch(0, 0, array('published_at'), array('ASC'));
        }
        $context->comments = $comments->getArray();
        $context->comments_voted = $this->getModel()->getGateway('Vote')->getPostsVoted(array_keys($context->comments), $this->getUser()->id);
        $context->parent_entity = $this->Content_ParentPost($context->entity, false);
        $context->modal = $context->getRequest()->asBool('modal');
        $context->addTemplate('comment_comments');
    }
}