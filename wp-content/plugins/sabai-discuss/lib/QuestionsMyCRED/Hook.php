<?php
class Sabai_Addon_QuestionsMyCRED_Hook extends myCRED_Hook
{
    public function __construct($hook_prefs, $type = 'mycred_default')
    {
        parent::__construct(
            array(
                'id' => 'hook_sabai_questions',
                'defaults' => array(
                    'sabai_vote_question' => array(
                        'creds' => 1,
                        'log' => '%plural% for voting up a question',
                        'limit' => '0/x'
                    ),
                    'sabai_vote_down_question' => array(
                        'creds' => -1,
                        'log' => '%plural% for voting down a question',
                        'limit' => '0/x'
                    ),
                    'sabai_unvote_question' => array(
                        'creds' => -1,
                        'log' => '%plural% for unvoting a question',
                        'limit' => '0/x'
                    ),
                    'sabai_question_voted' => array(
                        'creds' => 1,
                        'log' => '%plural% for your question voted up',
                        '_log' => sprintf('%s (reversal)', '%plural% for your question voted up'),
                        'limit' => '0/x'
                    ),
                    'sabai_question_voted_down' => array(
                        'creds' => -1,
                        'log' => '%plural% for your question voted down',
                        '_log' => sprintf('%s (reversal)', '%plural% for your question voted down'),
                        'limit' => '0/x'
                    ),
                    'sabai_vote_answer' => array(
                        'creds' => 1,
                        'log' => '%plural% for voting up an asnwer',
                        'limit' => '0/x'
                    ),
                    'sabai_vote_down_answer' => array(
                        'creds' => -1,
                        'log' => '%plural% for voting down an answer',
                        'limit' => '0/x'
                    ),
                    'sabai_unvote_answer' => array(
                        'creds' => -1,
                        'log' => '%plural% for unvoting an answer',
                        'limit' => '0/x'
                    ),
                    'sabai_answer_voted' => array(
                        'creds' => 1,
                        'log' => '%plural% for your answer voted up',
                        '_log' => sprintf('%s (reversal)', '%plural% for your answer voted up'),
                        'limit' => '0/x'
                    ),
                    'sabai_answer_voted_down' => array(
                        'creds' => -1,
                        'log' => '%plural% for your answer voted down',
                        '_log' => sprintf('%s (reversal)', '%plural% for your answer voted down'),
                        'limit' => '0/x'
                    ),
                    'sabai_accept_answer' => array(
                        'creds' => 1,
                        'log' => '%plural% for accepting an answer',
                        'limit' => '0/x'
                    ),
                    'sabai_unaccept_answer' => array(
                        'creds' => -1,
                        'log' => '%plural% for unaccepting an answer',
                        'limit' => '0/x'
                    ),
                    'sabai_answer_accepted' => array(
                        'creds' => 1,
                        'log' => '%plural% for your answer accepted',
                        '_log' => sprintf('%s (reversal)', '%plural% for your answer accepted'),
                        'limit' => '0/x',
                    ),
                ),   
            ),
            $hook_prefs,
            $type
        );
    }

    public function run()
    {
        add_action('sabai_questions_answer_accepted', array($this, 'onAnswerAccepted'), 10, 3);
        add_action('sabai_voting_content_questions_entity_voted_updown', array($this, 'onQuestionVoted'), 10, 2);
        add_action('sabai_voting_content_questions_answers_entity_voted_updown', array($this, 'onAnswerVoted'), 10, 2);
    }
    
    public function onAnswerAccepted($question, $answer, $score)
    {
        if (!$sabai = Sabai::exists()) return;
        
        if ($answer->getAuthorId() === $sabai->getUser()->id) return;

        if ($score) {
            $source = array(
                'id' => 'sabai_accept_answer',
                'creds' => $this->prefs['sabai_accept_answer']['creds'],
                'log' => $this->prefs['sabai_accept_answer']['log'],
          );
            $target = array(
                'id' => 'sabai_answer_accepted',
                'creds' => $this->prefs['sabai_answer_accepted']['creds'],
                'log' => $this->prefs['sabai_answer_accepted']['log'],
          );
        } else {
            $source = array(
                'id' => 'sabai_unaccept_answer',
                'creds' => $this->prefs['sabai_unaccept_answer']['creds'],
                'log' => $this->prefs['sabai_unaccept_answer']['log'],
            );
            $target = array(
                'id' => '_sabai_answer_accepted',
                'creds' => -1 * $this->prefs['sabai_answer_accepted']['creds'],
                'log' => $this->prefs['sabai_answer_accepted']['_log'],
          );
        }

        if (!$sabai->getUser()->isAnonymous()
            && !$this->core->exclude_user($sabai->getUser()->id)
            && !$this->over_hook_limit($id, $id)
        ) {
            $this->core->add_creds(
                $source['id'],
                $sabai->getUser()->id,
                $source['creds'],
                $source['log'],
                $answer->getId(),
                '',
                $this->mycred_type
          );
        }
        
        if (!$answer->getAuthor()->isAnonymous()
            && !$this->core->exclude_user($answer->getAuthorId())
            && !$this->over_hook_limit($id, $id)
        ) {
            $this->core->add_creds(
                $target['id'],
                $answer->getAuthorId(),
                $target['creds'],
                $target['log'],
                $answer->getId(),
                '',
                $this->mycred_type
          );
        }
    }
    
    public function onQuestionVoted($question, $results)
    {
        if (!$sabai = Sabai::exists()) return;
        
        if ($question->getAuthorId() === $sabai->getUser()->id) return;
        
        // Undoing vote?
        if ($results['prev_value'] !== false) {
            if (!empty($this->prefs['sabai_unvote_question']['creds'])
                && !$sabai->getUser()->isAnonymous()
                && !$this->core->exclude_user($sabai->getUser()->id)
                && !$this->over_hook_limit('sabai_unvote_question', 'sabai_unvote_question')
            ) {
                $this->core->add_creds(
                    'sabai_unvote_question',
                    $sabai->getUser()->id,
                    $this->prefs['sabai_unvote_question']['creds'],
                    $this->prefs['sabai_unvote_question']['log'],
                    $question->getId(),
                    '',
                    $this->mycred_type
              );
            }
            if ($results['prev_value'] == 1 || $results['prev_value'] == -1) {
                $target_id = $results['prev_value'] == 1 ? 'sabai_question_voted' : 'sabai_question_voted_down';
                if (!empty($this->prefs[$target_id]['creds'])
                    && !$question->getAuthor()->isAnonymous()
                    && !$this->core->exclude_user($question->getAuthorId())
                ) {
                    $this->core->add_creds(
                        '_' . $target_id,
                        $question->getAuthorId(),
                        -1 * $this->prefs[$target_id]['creds'], // undo points added by previous vote
                        $this->prefs[$target_id]['_log'],
                        $question->getId(),
                        '',
                        $this->mycred_type
                  );
                }
            }
        }
        
        // Reflect current vote
        if ($results['value'] == 1 || $results['value'] == -1) {
            $target_id = $results['value'] == 1 ? 'sabai_question_voted' : 'sabai_question_voted_down';
            if (!empty($this->prefs[$target_id]['creds'])
                && !$question->getAuthor()->isAnonymous()
                && !$this->core->exclude_user($question->getAuthorId())
                && !$this->over_hook_limit($target_id, $target_id)
            ) {
                $this->core->add_creds(
                    $target_id,
                    $question->getAuthorId(),
                    $this->prefs[$target_id]['creds'],
                    $this->prefs[$target_id]['log'],
                    $question->getId(),
                    '',
                    $this->mycred_type
              );
            }
            $source_id = $results['value'] == 1 ? 'sabai_vote_question' : 'sabai_vote_down_question';
            if (!empty($this->prefs[$source_id]['creds'])
                && !$sabai->getUser()->isAnonymous()
                && !$this->core->exclude_user($sabai->getUser()->id) 
                && !$this->over_hook_limit($source_id, $source_id)
            ) {
                $this->core->add_creds(
                    $source_id,
                    $sabai->getUser()->id,
                    $this->prefs[$source_id]['creds'],
                    $this->prefs[$source_id]['log'],
                    $question->getId(),
                    '',
                    $this->mycred_type
              );
            }
        }
    }
    
    public function onAnswerVoted($answer, $results)
    {
        if (!$sabai = Sabai::exists()) return;
        
        if ($answer->getAuthorId() === $sabai->getUser()->id) return;
        
        // Undoing vote?
        if ($results['prev_value'] !== false) {
            if (!empty($this->prefs['sabai_unvote_answer']['creds'])
                && !$sabai->getUser()->isAnonymous()
                && !$this->core->exclude_user($sabai->getUser()->id) 
                && !$this->over_hook_limit('sabai_unvote_answer', 'sabai_unvote_answer')
            ) {
                $this->core->add_creds(
                    'sabai_unvote_answer',
                    $sabai->getUser()->id,
                    $this->prefs['sabai_unvote_answer']['creds'],
                    $this->prefs['sabai_unvote_answer']['log'],
                    $answer->getId(),
                    '',
                    $this->mycred_type
              );
            }
            if ($results['prev_value'] == 1 || $results['prev_value'] == -1) {
                $target_id = $results['prev_value'] == 1 ? 'sabai_answer_voted' : 'sabai_answer_voted_down';
                if (!empty($this->prefs[$target_id]['creds'])
                    && !$answer->getAuthor()->isAnonymous()
                    && !$this->core->exclude_user($answer->getAuthorId())
                ) {
                    $this->core->add_creds(
                        '_' . $target_id,
                        $answer->getAuthorId(),
                        -1 * $this->prefs[$target_id]['creds'], // undo points added by previous vote
                        $this->prefs[$target_id]['_log'],
                        $answer->getId(),
                        '',
                        $this->mycred_type
                  );
                }
            }
        }
        
        // Reflect current vote
        if ($results['value'] == 1 || $results['value'] == -1) {
            $target_id = $results['value'] == 1 ? 'sabai_answer_voted' : 'sabai_answer_voted_down';
            if (!empty($this->prefs[$target_id]['creds'])
                && !$answer->getAuthor()->isAnonymous()
                && !$this->core->exclude_user($answer->getAuthorId())
                && !$this->over_hook_limit($target_id, $target_id)
            ) {
                $this->core->add_creds(
                    $target_id,
                    $answer->getAuthorId(),
                    $this->prefs[$target_id]['creds'],
                    $this->prefs[$target_id]['log'],
                    $answer->getId(),
                    '',
                    $this->mycred_type
              );
            }
            $source_id = $results['value'] == 1 ? 'sabai_vote_answer' : 'sabai_vote_down_answer';
            if (!empty($this->prefs[$source_id]['creds'])
                && !$sabai->getUser()->isAnonymous()
                && !$this->core->exclude_user($sabai->getUser()->id) 
                && !$this->over_hook_limit($source_id, $source_id)
            ) {
                $this->core->add_creds(
                    $source_id,
                    $sabai->getUser()->id,
                    $this->prefs[$source_id]['creds'],
                    $this->prefs[$source_id]['log'],
                    $answer->getId(),
                    '',
                    $this->mycred_type
              );
            }
        }
    }
    
    public function preferences()
    {
        foreach ($this->prefs as $id => $prefs) {
            $this->_printPreferences($id, $prefs);
        }
    }
    
    protected function _printPreferences($id, $prefs)
    {
?>
<label for="<?php echo $this->field_id(array($id, 'creds'));?>" class="subheader"><?php echo $this->core->template_tags_general($prefs['log']);?></label>
<ol>
    <li>
        <div class="h2">
            <input type="text" name="<?php echo $this->field_name(array($id, 'creds'));?>" id="<?php echo $this->field_id(array($id, 'creds'));?>" value="<?php echo $this->core->number($prefs['creds']);?>" size="8" />
        </div>
    </li>
    <li class="empty">&nbsp;</li>
    <li>
        <label for="<?php echo $this->field_id(array($id, 'limit'));?>"><?php echo __('Limit', 'sabai-discuss');?></label>
        <?php echo $this->hook_limit_setting($this->field_name(array($id, 'limit')), $this->field_id(array($id, 'limit')), $prefs['limit']);?>
    </li>
    <li class="empty">&nbsp;</li>
    <li>
        <label for="<?php echo $this->field_id(array($id, 'log'));?>"><?php echo __('Log template', 'sabai-discuss');?></label>
        <div class="h2">
            <input type="text" name="<?php echo $this->field_name(array($id, 'log'));?>" id="<?php echo $this->field_id(array($id, 'log'));?>" value="<?php echo esc_attr($prefs['log']);?>" class="long" />
        </div>
        <span class="description"><?php echo $this->available_template_tags(array('general'));?></span>
    </li>
<?php if (isset($prefs['_log'])):?>
    <li class="empty">&nbsp;</li>
    <li>
        <label for="<?php echo $this->field_id(array($id, '_log'));?>"><?php echo __('Log template (reversal)', 'sabai-discuss');?></label>
        <div class="h2">
            <input type="text" name="<?php echo $this->field_name(array($id, '_log'));?>" id="<?php echo $this->field_id(array($id, '_log'));?>" value="<?php echo esc_attr($prefs['_log']);?>" class="long" />
        </div>
        <span class="description"><?php echo $this->available_template_tags(array('general'));?></span>
    </li>
<?php endif;?>
</ol>
<?php
    }
    
    public function sanitise_preferences($data)
    {
        foreach (array_keys($this->prefs) as $id) {
            if (isset($this->prefs[$id]['limit'])
                && isset($data[$id]['limit'])
                && isset($data[$id]['limit_by'])
            ){
                $limit = sanitize_text_field($data[$id]['limit']);
                if ($limit == '') $limit = 0;
                $data[$id]['limit'] = $limit . '/' . $data[$id]['limit_by'];
                unset($data[$id]['limit_by']);    
            }
        }
        return $data;                
    }
}