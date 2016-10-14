<?php
class Sabai_Addon_Comment_Controller_EditComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  $("#sabai-comment-%d").html(result.comment_html).effect("highlight", {}, 3000).sabai();
  $(SABAI).trigger("comment_comment_edited.sabai", result);
}', $context->comment->id);
        $form = array(
            'body' => array(
                '#type' => 'textarea',
                '#rows' => 2,
                '#required' => true,
                '#default_value' => $context->comment->body,
            ),
        );
        if ($this->getUser()->isAdministrator()) {
            $form['disable_vote'] = array(
                '#type' => 'hidden',
                '#default_value' => $context->comment->vote_disabled,
            );
        }
        $this->_ajaxOnCancel = sprintf('function (target) {
    jQuery(\'#sabai-comment-%d\').find(\'.sabai-comment-main\').show();target.text(\'\').hide();
}', $context->comment->id);
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (md5($form->values['body']) !== md5($context->comment->body)) {
            $context->comment->body = $form->values['body'];
            $context->comment->body_html = $this->Comment_Filter($form->values['body']);
            $context->comment->edit_last_at = time();
            $context->comment->edit_last_by = $this->getUser()->id;
            $context->comment->edit_count++;
        }
        if (isset($form->settings['disable_vote'])) {
            $context->comment->vote_disabled = !empty($form->values['disable_vote']);
        }
        if (isset($form->settings['disable_flag'])) {
            $context->comment->flag_disabled = !empty($form->values['disable_flag']);
        }
        $this->getModel()->commit();
        $this->Action('comment_submit_comment_success', array($context->comment, /*$isEdit*/ true, $context->entity));

        $voted = $this->getModel('Vote')->postId_is($context->comment->id)->userId_is($this->getUser()->id)->count();
        $context->setSuccess($this->Entity_Url($context->entity, '', array(), 'sabai-comment-' . $context->comment->id))
            ->setSuccessAttributes(array(
                'comment_html' => $this->Comment_Render($context->comment->toArray(), $context->entity, null, $voted ? false : null, $context->getContainer() === '#sabai-modal', null),
            ));
    }
}