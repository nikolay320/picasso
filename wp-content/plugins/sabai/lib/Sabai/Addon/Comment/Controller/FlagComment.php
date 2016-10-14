<?php
class Sabai_Addon_Comment_Controller_FlagComment extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        // Check if the current user has already voted
        $count = $this->getModel('Vote')->postId_is($context->comment->id)->userId_is($this->getUser()->id)->count();
        if ($count) {
            $context->setError(__('You have already voted for or flagged this comment', 'sabai'));
            return;
        }
        
        // Init form
        $form = array();
        $form['value'] = array(
            '#type' => 'radios',
            '#options' => array(
                Sabai_Addon_Comment::VOTE_FLAG_VALUE_SPAM => __('It is spam', 'sabai'),
                Sabai_Addon_Comment::VOTE_FLAG_VALUE_OFFENSIVE => __('It contains offensive language or content', 'sabai'),
                Sabai_Addon_Comment::VOTE_FLAG_VALUE_OFFTOPIC => __('It does not belong here', 'sabai'),
            ),
            '#title' => __('I am flagging this comment as', 'sabai'),
            '#required' => true,
            '#default_value' => Sabai_Addon_Comment::VOTE_FLAG_VALUE_SPAM,
        );
        $this->_submitButtons['submit'] = array(
            '#value' => __('Flag Comment', 'sabai'),
        );        
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
    target.hide();
    var comment = $("#sabai-comment-%d");
    comment.find(".sabai-comment-vote, .sabai-comment-flag").remove().end()
        .find(".sabai-comment-flags").show().find("span").html("<i class=\"fa fa-flag\"></i> " + result.flag.count + " (" + result.flag.sum + ")");
    if (result.hide) {
        comment.addClass("sabai-comment-hidden");
    }
}', $context->comment->id);
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        // Cast vote
        $vote = $context->comment->createVote()->markNew();
        $vote->user_id = $this->getUser()->id;
        $vote->tag = 'flag';
        $vote->value = $form->values['value'];
        $vote->commit();
        
        // Calculate results
        $results = $context->comment->updateVoteStat('flag');
        if (!$context->comment->isHidden()) {
            // Hide comment if the total flag score exceeds spam threshold 
            if ($context->comment->flag_sum > $this->getAddon()->getConfig('spam', 'threshold')) {
                if ($context->comment->isFeatured()) {
                    // Update featured comments so that this comment will no longer be displayed as featured
                    $update_featured = true;
                }
                $context->comment->status = Sabai_Addon_Comment::POST_STATUS_HIDDEN;
                $context->comment->hidden_at = time();
                $notify_comment_spam = true;
            }
        }
        $context->comment->commit();
        if (!empty($update_featured)) {
            $this->getModel()->getGateway('Post')->updateFeaturedByEntity($context->entity->getId());
        }

        // Send success response
        $context->setSuccess($this->Entity_Url($context->entity))
            ->setSuccessAttributes(array(
                'flag' => $results,
                'hide' => $context->comment->isHidden() && $this->HasPermission($context->entity->getBundleName() . '_manage')
            ))
            ->clearFlash();
        
        // Notify that the comment has been flagged as spam
        if (!empty($notify_comment_spam)) {
            $this->Action('comment_flagged_as_spam', array($context->comment, $context->entity));
        }
    }
}