<?php
class Sabai_Addon_Comment_Controller_HideComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $this->_cancelUrl = $this->Entity_Url($context->entity);       
        $this->_ajaxOnSuccess = sprintf(
            'function (result, target, trigger) {
  target.hide();
  $("#sabai-comment-%d").toggleClass("sabai-comment-hidden", result.hidden).find(".sabai-comment-hide").text(result.hidden ? "%s" : "%s");
  $(SABAI).trigger("comment_comment_hidden.sabai", result);
}', 
            $context->comment->id,
            __('Unhide this comment', 'sabai'),
            __('Hide this comment', 'sabai')
        );
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-alert sabai-alert-warning">%s</div>',
            $context->comment->isHidden()
                ? __('Are you sure you want to unhide this comment?', 'sabai')
                : __('Hidden comments will not be visible to users without the "Moderate comments" permission. Are you sure you want to hide this comment?', 'sabai')
        );
        if (!$context->comment->isHidden()) {
            $this->_submitButtons[] = array('#value' => __('Hide Comment', 'sabai'));
        } else {
            $this->_submitButtons[] = array('#value' => __('Unhide Comment', 'sabai'));
        }

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!$context->comment->isHidden()) {
            $context->comment->status = Sabai_Addon_Comment::POST_STATUS_HIDDEN;
            $context->comment->hidden_at = time();
            $context->comment->hidden_by = $this->getUser()->id;
        } else {
            // Clear flags and update stats for the comment
            $this->getModel('Vote')->postId_is($context->comment->id)->tag_is('flag')->fetch()->delete(true);
            $context->comment->status = Sabai_Addon_Comment::POST_STATUS_PUBLISHED;
            $context->comment->updateVoteStat('flag');
            $context->comment->hidden_by = 0;
        }
        $context->comment->commit();
        
        // Update featured comments for the entity
        $this->getModel()->getGateway('Post')->updateFeaturedByEntity($context->entity->getId());

        $context->setSuccess($this->Entity_Url($context->entity))
            ->setSuccessAttributes(array(
                'hidden' => $context->comment->isHidden(),
            ));
    }
}