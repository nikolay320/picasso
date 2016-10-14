<?php
class Sabai_Addon_Comment_Controller_AddComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons[] = array('#value' => __('Add Comment', 'sabai'), '#btn_type' => 'primary');
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  $("#sabai-comment-comments-%1$d").append(result.comment_html).show().find("> li").last().effect("highlight", {}, 3000).sabai();
  $("#sabai-comment-comments-%1$d-add").show();
  $(SABAI).trigger("comment_comment_added.sabai", result);
}', $context->entity->getId());
        $this->_ajaxOnCancel = sprintf('function (cancel, target) {jQuery(\'#sabai-comment-comments-%d-add\').show();}', $context->entity->getId());
        $form = array(
            'body' => array(
                '#type' => 'textarea',
                '#rows' => 2,
                '#required' => true,
            ),
        );
        if ($this->getUser()->isAdministrator()) {
            $form['disable_flag'] = array(
                '#type' => 'hidden',
                '#default_value' => true,
            );
        }

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $comment = $this->getModel()->create('Post')->markNew();
        $comment->entity_id = $context->entity->getId();
        $comment->entity_bundle_name = $context->entity->getBundleName();
        $comment->body = $form->values['body'];
        $comment->body_html = $this->Comment_Filter($form->values['body']);
        $comment->User = $this->getUser();
        $comment->status = Sabai_Addon_Comment::POST_STATUS_PUBLISHED;
        $comment->published_at = time();
        if (isset($form->settings['disable_vote'])) {
            $comment->vote_disabled = !empty($form->values['disable_vote']);
        }
        if (isset($form->settings['disable_flag'])) {
            $comment->flag_disabled = !empty($form->values['disable_flag']);
        }
        $this->getModel()->commit();
        
        // Update featured comments for the entity
        $this->getModel()->getGateway('Post')->updateFeaturedByEntity($context->entity->getId());
        
        $this->Action('comment_submit_comment_success', array($comment, /*$isEdit*/ false, $context->entity));
        $context->setSuccess($this->Entity_Url($context->entity, '', array(), 'sabai-comment-' . $comment->id))
            ->setSuccessAttributes(array(
                'comment_html' => $this->Comment_Render($comment->toArray(), $context->entity, null, null, $context->getContainer() === '#sabai-modal'),
            ));
    }
}