<?php
class Sabai_Addon_Comment_Controller_Admin_EditComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $form = array(
            'body' => array(
                '#type' => 'textarea',
                '#rows' => 5,
                '#required' => true,
                '#default_value' => $context->comment->body,
            ),
        );
        $form['disable_vote'] = array(
            '#type' => 'checkbox',
            '#title' => __('Disable voting on this comment', 'sabai'),
            '#default_value' => $context->comment->vote_disabled,
        );
        $form['disable_flag'] = array(
            '#type' => 'checkbox',
            '#title' => __('Disable flagging this comment', 'sabai'),
            '#default_value' => $context->comment->flag_disabled,
        );
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $context->comment->body = $form->values['body'];
        $context->comment->body_html = $this->Comment_Filter($form->values['body']);
        $context->comment->vote_disabled = !empty($form->values['disable_vote']);
        $context->comment->flag_disabled = !empty($form->values['disable_flag']);
        $this->getModel()->commit();
        $this->Action('comment_submit_comment_success', array($context->comment, /*$isEdit*/ true));
    }
}