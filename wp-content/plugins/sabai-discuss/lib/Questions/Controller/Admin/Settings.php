<?php
class Sabai_Addon_Questions_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig();
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai-discuss'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai-discuss');
        $sorts = array(
            'newest' => __('Newest First', 'sabai-discuss'),
            'oldest' => __('Oldest First', 'sabai-discuss'),
            'active' => __('Recently Active', 'sabai-discuss'),
            'votes' => __('Most Votes', 'sabai-discuss'),
            'views' => __('Most Viewed', 'sabai-discuss'),
            'answers' => _x('Most Answered', 'sort', 'sabai-discuss'),
            'random' => __('Random', 'sabai-discuss'),
        ) + $this->_getSortableFields($this->getAddon()->getQuestionsBundleName());
        $answer_sorts = array(
            'newest' => __('Newest First', 'sabai-discuss'),
            'oldest' => __('Oldest First', 'sabai-discuss'),
            'active' => __('Recently Active', 'sabai-discuss'),
            'votes' => __('Most Votes', 'sabai-discuss'),
            'random' => __('Random', 'sabai-discuss'),
        ) + $this->_getSortableFields($this->getAddon()->getAnswersBundleName());
        $form = array('#tree' => true);
        $form['front'] = array(
            '#title' => __('Display Settings', 'sabai-discuss'),
            'perpage' => array(
                '#type' => 'textfield',
                '#title' => __('Number of posts per page', 'sabai-discuss'),
                '#default_value' => $config['front']['perpage'],
                '#size' => 6,
                '#integer' => true,
                '#required' => true,
                '#max_value' => 100,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-discuss'), 100),
            ),
            'answer_perpage' => array(
                '#type' => 'number',
                '#title' => __('Answers per page', 'sabai-discuss'),
                '#default_value' => isset($config['front']['answer_perpage']) ? $config['front']['answer_perpage'] : 20,
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#display_unrequired' => true,
                '#max_value' => 100,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-discuss'), 100),
            ),
            'sorts' => array(
                '#type' => 'checkboxes',
                '#default_value' => isset($config['front']['sorts']) ? $config['front']['sorts'] : array_keys($sorts),
                '#title' => __('Questions sorting options', 'sabai-discuss'),
                '#options' => $sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['front']['sort'],
                '#title' => __('Questions default sorting order', 'sabai-discuss'),
                '#options' => $sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'answer_sorts' => array(
                '#type' => 'checkboxes',
                '#default_value' => isset($config['front']['answer_sorts']) ? $config['front']['answer_sorts'] : array_keys($answer_sorts),
                '#title' => __('Answers sorting options', 'sabai-discuss'),
                '#options' => $answer_sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'answer_sort' => array(
                '#type' => 'radios',
                '#default_value' => isset($config['front']['answer_sort']) ? $config['front']['answer_sort'] : array_shift(array_keys($answer_sorts)),
                '#title' => __('Answers default sorting order', 'sabai-discuss'),
                '#options' => $answer_sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'feature' => array(
                '#type' => 'yesno',
                '#title' => __('Stick featured posts to the top', 'sabai-discuss'),
                '#default_value' => !empty($config['front']['feature']),
            ),
        );
        $form['reputation'] = array(
            '#title' => __('Reputation Settings', 'sabai-discuss'),
            'info' => array(
                '#weight' => 1,
                '#type' => 'markup',
                '#markup' => '<p>' . __('Reputation is a rough measurement of how much the community trusts you; it is earned by convincing your peers that you know what you\'re talking about. Here you can configure reputation users gain or lose on certain events or actions.', 'sabai-discuss') . '</p>',
            ),
            'points' => array(
                '#weight' => 2,
                '#class' => 'sabai-form-group',
                'question_voted' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['question_voted']) ? $config['reputation']['question_voted'] : @$config['reputation']['points']['question_voted'],
                    '#field_prefix' => __('One of your questions is voted up:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'question_voted_down' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['question_voted_down']) ? $config['reputation']['question_voted_down'] : @$config['reputation']['points']['question_voted_down'],
                    '#field_prefix' => __('One of your questions is voted down:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'answer_voted' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['answer_voted']) ? $config['reputation']['answer_voted'] : @$config['reputation']['points']['answer_voted'],
                    '#field_prefix' => __('One of your answers is voted up:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'answer_voted_down' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['answer_voted_down']) ? $config['reputation']['answer_voted_down'] : @$config['reputation']['points']['answer_voted_down'],
                    '#field_prefix' => __('One of your answers is voted down:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'answer_accepted' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['answer_accepted']) ? $config['reputation']['answer_accepted'] : @$config['reputation']['points']['answer_accepted'],
                    '#field_prefix' => __('One of your answers become accepted:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'answer_accepted_user' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['answer_accepted_user']) ? $config['reputation']['answer_accepted_user'] : @$config['reputation']['points']['answer_accepted_user'],
                    '#field_prefix' => __('You accept an answer posted to your question:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'answer_vote_down' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['answer_vote_down']) ? $config['reputation']['answer_vote_down'] : @$config['reputation']['points']['answer_vote_down'],
                    '#field_prefix' => __('You vote an answer down:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'question_unvoted' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['points']['question_unvoted']) ? $config['reputation']['points']['question_unvoted'] : -1,
                    '#field_prefix' => __('You unvote a question:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'answer_unvoted' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['points']['answer_unvoted']) ? $config['reputation']['points']['answer_unvoted'] : -1,
                    '#field_prefix' => __('You cancel an upvote on an answer:', 'sabai-discuss'),
                    '#size' => 6,
                    '#integer' => true,
                    '#required' => true,
                ),
                'spam' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['reputation']['spam']) ? $config['reputation']['spam'] : @$config['reputation']['points']['spam'],
                    '#field_prefix' => __('One of your questions or answers is deleted as being spam:', 'sabai-discuss'),
                    '#size' => 5,
                    '#integer' => true,
                    '#required' => true,
                ),
            ),
        );
        $form['spam'] = array(
            '#title' => __('Spam Settings', 'sabai-discuss'),
            'threshold' => array(
                '#type' => 'textfield',
                '#title' => __('Spam score threshold', 'sabai-discuss'),
                '#field_suffix' => '+ 0.3 x ' . __('number of votes', 'sabai-discuss'),
                '#description' => __('When a post is flagged, the post is assigned a "spam score". Posts with spam scores exceeding the threshold value are marked as spam and moved to trash automatically by the system. Also, posts with higher number of votes will have higher threshold. For example, if the value set here is 11, and a post has 10 votes, then the spam score threshold for the post will be 14 (11 + 0.3 x 10).', 'sabai-discuss'),
                '#default_value' => $config['spam']['threshold'],
                '#size' => 4,
                '#integer' => true,
                '#required' => true,
            ),
            'auto_delete' => array(
                '#type' => 'checkbox',
                '#title' => __('Auto-delete spam', 'sabai-discuss'),
                '#default_value' => $config['spam']['auto_delete'],
                '#description' => __('When checked, posts that have been marked as spam will be deleted by the system after the period of time specified in the "Delete Spam After" option.', 'sabai-discuss'),
            ),
            'delete_after' => array(
                '#type' => 'textfield',
                '#default_value' => $config['spam']['delete_after'],
                '#field_prefix' => __('Delete spam after:', 'sabai-discuss'),
                '#description' => __('Enter the number of days the system will wait before auto-deleting posts marked as spam.', 'sabai-discuss'),
                '#field_suffix' => __('days', 'sabai-discuss'),
                '#size' => 4,
                '#integer' => true,
                '#states' => array(
                    'visible' => array(
                        'input[name="spam[auto_delete][]"]' => array('value' => 1),
                    ),
                ),
            ),
        );
        $form['label'] = array(
            '#title' => __('Labels', 'sabai-discuss'),
            'categories' => array(
                '#title' => __('Categories', 'sabai-discuss'),
                '#description' => sprintf(__('Enter the label to be used for "%s"', 'sabai-discuss'), __('Categories', 'sabai-discuss')),
                '#type' => 'textfield',
                '#default_value' => @$config['label']['categories'],
                '#required' => true,
                '#size' => 40,
            ),
            'category' => array(
                '#title' => __('Category', 'sabai-discuss'),
                '#description' => sprintf(__('Enter the label to be used for "%s"', 'sabai-discuss'), __('Category', 'sabai-discuss')),
                '#type' => 'textfield',
                '#default_value' => @$config['label']['category'],
                '#required' => true,
                '#size' => 40,
            ),
            'tags' => array(
                '#title' => __('Tags', 'sabai-discuss'),
                '#description' => sprintf(__('Enter the label to be used for "%s"', 'sabai-discuss'), __('Tags', 'sabai-discuss')),
                '#type' => 'textfield',
                '#default_value' => @$config['label']['tags'],
                '#required' => true,
                '#size' => 40,
            ),
            'tag' => array(
                '#title' => __('Tag', 'sabai-discuss'),
                '#description' => sprintf(__('Enter the label to be used for "%s"', 'sabai-discuss'), __('Tag', 'sabai-discuss')),
                '#type' => 'textfield',
                '#default_value' => @$config['label']['tag'],
                '#required' => true,
                '#size' => 40,
            ),
        );
        $form['page_title'] = array(
            '#title' => __('Page Title Settings', 'sabai-discuss'),
            'category' => array(
                '#title' => __('Single category page', 'sabai-discuss'),
                '#type' => 'textfield',
                '#default_value' => $config['page_title']['category'],
                '#required' => true,
                '#size' => 40,
            ),
            'tag' => array(
                '#title' => __('Single tag page', 'sabai-discuss'),
                '#type' => 'textfield',
                '#default_value' => $config['page_title']['tag'],
                '#required' => true,
                '#size' => 40,
            ),
        );
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $new_config = array(
            'front' => $form->values['front'],
            'reputation' => $form->values['reputation'],
            'spam' => $form->values['spam'],
            'label' => $form->values['label'],
            'page_title' => $form->values['page_title'],
        );       
        $this->getAddon()->saveConfig($new_config);
        $this->reloadAddons();
        $context->setSuccess('/' . $this->getAddon()->getSlug('questions') . '/settings');
    }
    
    private function _getSortableFields($bundleName)
    {
        $ret = array();
        foreach ($this->Entity_SortableFields($bundleName, true, false) as $field_name => $field) {
            $ret[$field_name] = $field['label'];
        }
        return $ret;
    }
}