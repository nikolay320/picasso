<?php
class Sabai_Addon_Voting_Controller_VoteEntityForm extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $settings = $this->Voting_TagSettings($context->voting_tag);
        
        if (!isset($settings['form_title']) || !isset($settings['form_options'])) {
            // Voting via form is not supported 
            $context->setBadRequestError();
            return;
        }
        
        // Init form
        $form = array();
        
        // Check if already voted
        $vote = $this->getModel('Vote')
            ->entityType_is($context->entity->getType()) 
            ->entityId_is($context->entity->getId())
            ->userId_is($this->getUser()->id)
            ->tag_is($context->voting_tag)
            ->fetchOne();        
        if ($vote) {
            if (isset($settings['form_redo_msg'])) {
                $form['#header'][] = sprintf(
                    '<div class="sabai-alert sabai-alert-warning">%s</div>',
                    sprintf($settings['form_redo_msg'], $this->Entity_BundleLabel($context->child_bundle ? $context->child_bundle : $context->bundle, true))
                );
            }
            $form['current'] = array(
                '#type' => 'item',
                '#title' => $settings['form_title'],
                '#markup' => Sabai::h($settings['form_options'][$vote->value]),
            );
            if (strlen($vote->comment)) {
                $form['current_comment'] = array(
                    '#type' => 'item',
                    '#title' => __('Comment', 'sabai'),
                    '#markup' => Sabai::h($vote->comment),
                );
            }
            $form['current'] = array(
                '#type' => 'item',
                '#title' => $settings['form_title'],
                '#markup' => $settings['form_options'][$vote->value],
            );
            $form['value'] = array('#type' => 'hidden', '#value' => $vote->value);
            $this->_submitButtons['submit'] = array(
                '#value' => isset($settings['form_redo_btn']) ? $settings['form_redo_btn'] : __('Redo Submit', 'sabai'),
            );
        } else {
            $form['value'] = array(
                '#type' => 'radios',
                '#options' => $settings['form_options'],
                '#title' => $settings['form_title'],
                '#default_value' => $settings['form_default_value'],
                '#required' => true,
            );
            if (isset($settings['form_other_option']) && isset($settings['form_options'][$settings['form_other_option']])) {
                $form['comment'] = array(
                    '#type' => 'textfield',
                    '#title' => __('Comment', 'sabai'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="value"]' => array('value' => $settings['form_other_option']),
                        ),
                    ),
                    '#required' => array(array($this, 'isCommentRequired'), array($settings['form_other_option'])),
                );
            }
            $this->_submitButtons['submit'] = array(
                '#value' => isset($settings['form_submit_btn'])
                    ? sprintf($settings['form_submit_btn'], $this->Entity_BundleLabel($context->child_bundle ? $context->child_bundle : $context->bundle, true))
                    : __('Submit', 'sabai'),
            );
        }
        $this->_ajaxOnSuccessFlash = true;
        $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {target.hide();}');
        if ($update_target_id = $context->getRequest()->asStr('update_target_id')) {
            $update_url = $this->Entity_Url($context->entity);
            $this->_ajaxOnSuccess = sprintf(
                'function (result, target, trigger) {
    target.hide();
    if (result.trashed) {
        $("#%1$s").fadeTo("fast", 0, function(){$(this).slideUp("medium", function(){$(this).remove();});});
    } else {
        SABAI.replace("#%1$s", "%2$s");
    }
}',
                Sabai::h($update_target_id),
                $update_url
            );
            $form['update_target_id'] = array('#type' => 'hidden', '#value' => $update_target_id);
        }
        
        return $form;
    }
    
    public function isCommentRequired($form, $valueThatRequiresComment)
    {
        return $form->values['value'] == $valueThatRequiresComment;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $results = $this->Voting_CastVote(
            $context->entity,
            $context->voting_tag,
            $form->values['value'], 
            isset($form->values['comment']) ? array('comment' => $form->values['comment']) : array()
        );
                
        // Display vote form if the previous vote has been undone
        if ($results['value'] === false) {
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);
            return;
        }
        
        // Content may have been hidden as a result of the vote
        if ($context->entity->isTrashed()) {
            $bundle = $this->Entity_Bundle($context->entity);
            $context->setSuccess($bundle->getPath()) // redirect to top
                ->setSuccessAttributes($results + array('trashed' => true));
            return;
        }
        $settings = $this->Voting_TagSettings($context->voting_tag);
        if (isset($settings['form_success_msg'])) {
            $context->addFlash($settings['form_success_msg']);
        }
        $context->setSuccess($this->Entity_Url($context->entity))
            ->setSuccessAttributes($results + array('trashed' => false));
    }
}