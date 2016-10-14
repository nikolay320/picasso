<?php
class Sabai_Addon_Questions_Controller_AcceptAnswer extends Sabai_Addon_Form_Controller
{
    private $_acceptedAnswers;
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Fetch the question to which the answer was posted 
        $context->question = $this->Content_ParentPost($context->entity, true);
        // Only the question author or users with accept any answer permission can accept the answer
         if (!$context->question
             || (!$this->Entity_IsAuthor($context->question, $this->getUser())
                 && !$this->HasPermission($context->entity->getBundleName() . '_accept_any'))
         ) {
            $context->setForbiddenError();
            return;
        }
        
        // Check to see if there are already answers accepted for this question
        $this->_acceptedAnswers = $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $context->child_bundle->name)
            ->fieldIs('content_parent', $context->question->getId())
            ->fieldIsNotNull('questions_answer_accepted', 'score')
            ->sortByField('questions_answer_accepted', 'DESC', 'score')
            ->fetch();
        
        // Accept if not already accpeted and the answer count is below threshold, otherwise unaccept the answer
        if (!isset($this->_acceptedAnswers[$context->entity->getId()])) {
            $icon_star = '<i style="color:orangered" class="fa fa-star"></i> ';
            $icon_star_empty = '<i class="fa fa-star-o"></i> ';
            $form['score'] = array(
                '#title' => Sabai::h(__('Rate the answer to accept', 'sabai-discuss')),
                '#type' => 'radios',
                '#options' => array(
                    3 => str_repeat($icon_star, 3) . __('Best Answer', 'sabai-discuss'),
                    2 => str_repeat($icon_star, 2) . $icon_star_empty . __('Great Answer', 'sabai-discuss'),
                    1 => $icon_star . str_repeat($icon_star_empty, 2) . __('Good Answer', 'sabai-discuss'),                    
                ),
                '#title_no_escape' => true,
                '#required' => true,
                '#weight' => 10,
                '#default_value' => 3,
            );
            if (count($this->_acceptedAnswers) >= 3) {
                $options = $options_desc = array();
                foreach ($this->_acceptedAnswers as $answer_id => $answer) {
                    $answer_score = (int)$answer->questions_answer_accepted[0]['score'];
                    $options[$answer_id] = sprintf(__('Answer by %s posted %s', 'sabai-discuss'), Sabai::h($this->Entity_Author($answer)->name), $this->getPlatform()->getHumanTimeDiff($answer->getTimestamp()));
                    $options_desc[$answer_id] = str_repeat($icon_star, $answer_score) . $this->Summarize($answer->getContent(), 70);
                }
                $form['#header'] = array(
                    '<div class="sabai-alert sabai-alert-warning">' . __('There are already 3 accepted answers accepted. You must unaccept one of them to accept the current answer.', 'sabai-discuss') . '</div>',
                );
                $form['answer_to_unaccept'] = array(
                    '#title' => __('Unaccept existing answer', 'sabai-discuss'),
                    '#type' => 'radios',
                    '#options' => $options,
                    '#options_description' => $options_desc,
                    '#required' => true,
                    '#weight' => 5,
                );
                // make the rating selection visible only when one of the existing answers is selected 
                $form['score']['#states'] = array(
                    'visible' => array(
                        'input[name="answer_to_unaccept"]' => array('value' => array_keys($this->_acceptedAnswers)),
                    ),
                );
            }
            
            $submit_label = __('Accept Answer', 'sabai-discuss');
        } else {
            // Already accepted so this will unaccept the answer
            $form['#header'][] = sprintf(
                '<div class="sabai-alert sabai-alert-warning">%s</div>',
                __('Do you really want to unaccept this answer?', 'sabai-discuss')
            );
            $submit_label = __('Unaccept Answer', 'sabai-discuss');
        }
        $this->_submitButtons['submit'] = array(
            '#value' => $submit_label,
            '#btn_type' => 'primary',
        );
        if ($update_target_id = $context->getRequest()->asStr('update_target_id')) {
            $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
    target.hide();
    SABAI.replace("#%s", "%s");
}', Sabai::h($update_target_id), $this->Entity_Url($context->entity));
            $form['update_target_id'] = array('#type' => 'hidden', '#value' => $update_target_id);
        }
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $score = empty($form->values['score']) ? 0 : $form->values['score'];
        if ($score) {
            if (count($this->_acceptedAnswers) >= 3) {
                if (empty($form->values['answer_to_unaccept'])
                    || !isset($this->_acceptedAnswers[$form->values['answer_to_unaccept']])
                ) {
                    $form->setError(__('Answer to unaccept must be a valid answer', 'sabai-discuss'));
                    return;
                }
                // Unaccept selected answer
                $this->Entity_Save(
                    $this->_acceptedAnswers[$form->values['answer_to_unaccept']],
                    array('questions_answer_accepted' => array('score' => 0, 'accepted_at' => 0))
                );
                $accepted_answer_count = count($this->_acceptedAnswers);
            } else {
                $accepted_answer_count = count($this->_acceptedAnswers) + 1;
            }
        } else {
            $accepted_answer_count = count($this->_acceptedAnswers) - 1;
            if ($accepted_answer_count < 0) {
                $accepted_answer_count = 0;
            }
        }
        // Update answer and question
        $answer = $this->Entity_Save($context->entity, array('questions_answer_accepted' => array('score' => $score, 'accepted_at' => $score ? time() : 0)));
        $question = $this->Entity_Save($context->question, array('questions_resolved' => $accepted_answer_count > 0 ? 1 : 0));

        $this->Action('questions_answer_accepted', array($question, $answer, $score, time()));
    }
}