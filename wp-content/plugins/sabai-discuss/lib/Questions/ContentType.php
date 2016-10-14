<?php
class Sabai_Addon_Questions_ContentType implements Sabai_Addon_Content_IContentType
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Questions $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function contentTypeGetInfo()
    {
        if ($this->_name === $this->_addon->getQuestionsBundleName()) {
            return array(
                'type' => 'questions',
                'path' => '/' . $this->_addon->getSlug('questions'),
                'admin_path' => '/' . strtolower($this->_addon->getName()),
                'label' => $this->_addon->getApplication()->_t(_n_noop('Questions', 'Questions', 'sabai-discuss'), 'sabai-discuss'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Question', 'Question', 'sabai-discuss'), 'sabai-discuss'),
                'permalink_path' => '/' . $this->_addon->getSlug('question'),
                'properties' => array(
                    'post_title' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 2,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 3,
                    ),
                    'post_user_id' => array(
                        'weight' => 9,
                    ),
                ),
                'fields' => array(
                    'questions_resolved' => array(
                        'type' => 'questions_resolved',
                        'settings' => array(),
                        'max_num_items' => 1, // Only 1 entry per entity should be created
                        'filter' => array(
                            'type' => 'questions_resolved',
                            'name' => 'questions_resolved',
                            'title' => __('Resolved/Unresolved', 'sabai-discuss'),
                            'col' => 2,
                            'weight' => 2,
                        ),
                    ),
                    'questions_closed' => array(
                        'type' => 'questions_closed',
                        'settings' => array(),
                        'max_num_items' => 1, // Only 1 entry per entity should be created
                        'filter' => array(
                            'type' => 'questions_open',
                            'name' => 'questions_open',
                            'title' => __('Open/Closed', 'sabai-discuss'),
                            'col' => 2,
                            'weight' => 2,
                        ),
                    ),
                ),
                'taxonomy_terms' => array(
                    $this->_addon->getTagsBundleName() => array(
                        'description' => __('Tags are words and phrases that you assign to questions to help keep the site well organized. Maximum of 5 tags may be assigned to each question.', 'sabai-discuss'),
                        'max_num_items' => 5,
                        'required' => false,
                        'widget' => 'taxonomy_tagging',
                        'weight' => 8,
                        'renderer_settings' => array(
                            'default' => array(
                                'taxonomy_terms' => array(
                                    'icon' => 'tag'
                                    ,
                                ),
                            ),
                        ),
                        'filter' => array(
                            'settings' => array('content_bundle' => 'questions'),
                        ),
                    ),
                    $this->_addon->getCategoriesBundleName() => array(
                        'required' => false,
                        'max_num_items' => 1,
                        'weight' => 7,
                        'renderer_settings' => array(
                            'default' => array(
                                'taxonomy_terms' => array(
                                    'icon' => 'folder-open',
                                ),
                            ),
                        ),
                        'filter' => array(
                            'settings' => array('content_bundle' => 'questions'),
                        ),
                    ),
                ),
                'comment_comments' => array(),
                'voting_favorite' => array('icon' => 'star'),
                'voting_flag' => true,
                'voting_updown' => true,
                'content_permissions' => array(
                    'close_own' => array(
                        'label' => __('Close own questions', 'sabai-discuss'),
                        'default' => true,
                        'guest_allowed' => true,
                    ),
                    'close_any' => array(
                        'label' => __('Close any question', 'sabai-discuss'),
                        'default' => false,
                    ),
                    'trash_own' => array(
                        'label' => __('Delete own questions (if unresolved and has no more than 1 answer)', 'sabai-discuss'),
                        'default' => true,
                    ),
                ),
                'content_body' => array(
                    'required' => false,
                    'hide_label' => true,
                    'widget_settings' => array('rows' => 25),
                    'weight' => 5,
                    'filter' => false,
                    'renderer_settings' => array(
                        'summary' => array(
                            'text' => array(
                                'trim' => array('enable' => true, 'length' => 100, 'marker' => '...'),
                            ),
                        ),
                    ),
                ),
                'content_featurable' => true,
                'content_guest_author' => array(
                    'weight' => 1,
                    'hide_label' => true,
                ),
                'content_previewable' => true,
                'social_shareable' => true,
                'filterable' => true,
            );
        } elseif ($this->_name === $this->_addon->getAnswersBundleName()) {
            return array(
                'type' => 'questions_answers',
                'path' => '/' . $this->_addon->getSlug('questions') . '/' . $this->_addon->getSlug('answers'),
                'admin_path' => '/' . strtolower($this->_addon->getName()) . '/answers',
                'parent' => $this->_addon->getQuestionsBundleName(),
                'label' => $this->_addon->getApplication()->_t(_n_noop('Answers', 'Answers', 'sabai-discuss'), 'sabai-discuss'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Answer', 'Answer', 'sabai-discuss'), 'sabai-discuss'),
                'properties' => array(
                    'post_title' => array(
                        'widget' => 'content_post_title_hidden', // disable title property field
                        'weight' => 2,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 3,
                    ),
                    'post_user_id' => array(
                        'weight' => 9,
                    ),
                ),
                'fields' => array(
                    'questions_answer_accepted' => array(
                        'type' => 'questions_answer_accepted',
                        'settings' => array(),
                        'max_num_items' => 1, // Only 1 entry per entity should be created
                        'filter' => array(
                            'type' => 'questions_answer_accepted',
                            'name' => 'questions_answer_accepted',
                            'title' => __('Accepted/Unaccepted', 'sabai-discuss'),
                            'col' => 2,
                            'weight' => 2,
                        ),
                    ),
                ),
                'comment_comments' => array(),
                'voting_favorite' => array('icon' => 'star'),
                'voting_flag' => true,
                'voting_updown' => true,
                'content_permissions' => array(
                    'trash_own' => array(
                        'label' => __('Delete own answers (if not yet accepted by the question author)', 'sabai-discuss'),
                        'default' => true,
                    ),
                    'accept_any' => array(
                        'label' => __('Accept any answer', 'sabai-discuss'),
                        'default' => false,
                    ),
                ),
                'content_body' => array(
                    'required' => true,
                    'hide_label' => true,
                    'widget_settings' => array('rows' => 15),
                    'weight' => 5,
                ),
                'content_guest_author' => array(
                    'hide_label' => true,
                    'weight' => 1,
                ),
                'content_previewable' => true,
                'filterable' => true,
            );
        }
    }
    
    public function contentTypeIsPostTrashable(Sabai_Addon_Content_Entity $entity, SabaiFramework_User $user)
    {
        if ($this->_name === $this->_addon->getQuestionsBundleName()) {
            return $this->_addon->isQuestionTrashable($entity, $user);
        } elseif ($this->_name === $this->_addon->getAnswersBundleName()) {
            return $this->_addon->isAnswerTrashable($entity, $user);
        }
    }
    
    public function contentTypeIsPostRoutable(Sabai_Addon_Content_Entity $entity, SabaiFramework_User $user)
    {
        return true;
    }
}
