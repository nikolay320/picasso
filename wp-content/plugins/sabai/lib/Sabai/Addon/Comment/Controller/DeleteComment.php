<?php
class Sabai_Addon_Comment_Controller_DeleteComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons[] = array('#value' => __('Delete Comment', 'sabai'), '#btn_type' => 'danger');
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  $("#sabai-comment-%1$d").fadeTo("fast", 0, function(){$(this).slideUp("fast", function(){$(this).remove(); $(SABAI).trigger("comment_comment_deleted.sabai", result);});});
}', $context->comment->id);
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-alert sabai-alert-warning">%s</div>',
            __('Are you sure you want to <em>permanently</em> delete this comment? This cannot be undone.', 'sabai')
        );

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $comment_is_featured = $context->comment->status == Sabai_Addon_Comment::POST_STATUS_FEATURED;
        $context->comment->markRemoved();
        $context->comment->commit();    
        if ($comment_is_featured) {
            // Update featured comments for the entity
            $this->getModel()->getGateway('Post')->updateFeaturedByEntity($context->entity->getId());
        }       
        $this->Action('comment_delete_comment_success', array($context->comment));
        $context->setSuccess($this->Entity_Url($context->entity));
    }
}