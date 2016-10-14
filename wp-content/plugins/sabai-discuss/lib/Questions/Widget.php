<?php
class Sabai_Addon_Questions_Widget implements Sabai_Addon_Widgets_IWidget
{
    private $_addon, $_name;
    
    public function __construct(Sabai_Addon_Questions $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function widgetsWidgetGetTitle()
    {
        switch ($this->_name) {
            case 'recent':
                return __('Recent Questions & Answers', 'sabai-discuss');
            case 'popular':
                return __('Popular Questions', 'sabai-discuss');
            case 'featured':
                return __('Featured Questions', 'sabai-discuss');
            case 'related':
                return __('Related Questions', 'sabai-discuss');
            case 'unanswered':
                return __('Unanswered Questions', 'sabai-discuss');
            case 'answers_accepted':
                return __('Accepted Answers', 'sabai-discuss');
            case 'tags':
                return __('Question Tags', 'sabai-discuss');
            case 'categories':
                return __('Question Categories', 'sabai-discuss');
            case 'search':
                return __('Search Questions & Answers', 'sabai-discuss');
            case 'askbtn':
                return __('Ask Button', 'sabai-discuss');
            case 'leaders':
                return __('Leader Board', 'sabai-discuss');
        }
    }
    
    public function widgetsWidgetGetSummary()
    {
        switch ($this->_name) {
            case 'recent':
                return __('Recently posted questions and answers', 'sabai-discuss');
            case 'popular':
                return __('Questions that reach all of the threshold criteria set for the widget', 'sabai-discuss');
            case 'featured':
                return __('Questions that are marked as featured', 'sabai-discuss');
            case 'related':
                return __('Related questions', 'sabai-discuss');
            case 'unanswered':
                return __('Questions without any accepted answer', 'sabai-discuss');
            case 'answers_accepted':
                return __('Answers that were recently accepted', 'sabai-discuss');
            case 'tags':
                return __('A list of tags used', 'sabai-discuss');
            case 'categories':
                return __('A list of categories', 'sabai-discuss');
            case 'search':
                return __('Search questions and answers form', 'sabai-discuss');
            case 'askbtn':
                return __('A call to action button', 'sabai-discuss');
            case 'leaders':
                return __('A list of users with highest reputation points', 'sabai-discuss');   
        }
    }
    
    public function widgetsWidgetGetSettings()
    {
        $settings = array(
            'no_cache' => array(
                '#type' => 'checkbox',
                '#title' => __('Do not cache output', 'sabai-discuss'),
                '#default_value' => false,
                '#weight' => 99,
            ),
        );
        switch ($this->_name) {
            case 'leaders':
                return $settings + array(
                    'addon' => array(
                        '#title' => __('Select add-on', 'sabai-discuss'),
                        '#options' => $options = $this->_addon->getApplication()->Questions_AddonList(),
                        '#type' => count($options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => $this->_addon->getName(),
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of users to show', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                );
            case 'recent':
                return $settings + array(
                    'addon' => array(
                        '#title' => __('Select add-on', 'sabai-discuss'),
                        '#options' => $options = array('' => __('All add-ons', 'sabai-discuss')) + $this->_addon->getApplication()->Questions_AddonList(),
                        '#type' => count($options) <= 2 ? 'hidden' : 'select',
                        '#default_value' => $this->_addon->getName(),
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of posts to show', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-discuss'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 100, 
                    ),
                );
            case 'featured':
            case 'unanswered':
                return $settings + array(
                    'bundle' => $this->_getSelectCategoryField(),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of posts to show', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort posts by', 'sabai-discuss'),
                        '#options' => array(
                            'post_published' => __('Date posted', 'sabai-discuss'),
                            'voting_updown.count' => __('Number of votes', 'sabai-discuss'),
                            'voting_updown.sum' => __('Total vote score', 'sabai-discuss'),
                            'content_children_count.value' => __('Number of answers', 'sabai-discuss'),
                            'voting_favorite.count' => __('Number of favorites', 'sabai-discuss'),
                            'post_views' => __('Number of views', 'sabai-discuss'),
                            '_random' => __('Random', 'sabai-discuss'),
                        ),
                        '#default_value' => '_random', 
                    ),
                );
            case 'related':
                return $settings + array(
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of posts to show', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort posts by', 'sabai-discuss'),
                        '#options' => array(
                            'post_published' => __('Date posted', 'sabai-discuss'),
                            'voting_updown.count' => __('Number of votes', 'sabai-discuss'),
                            'voting_updown.sum' => __('Total vote score', 'sabai-discuss'),
                            'content_children_count.value' => __('Number of answers', 'sabai-discuss'),
                            'voting_favorite.count' => __('Number of favorites', 'sabai-discuss'),
                            'post_views' => __('Number of views', 'sabai-discuss'),
                            '_random' => __('Random', 'sabai-discuss'),
                        ),
                        '#default_value' => '_random', 
                    ),
                );
            case 'popular':
                return $settings + array(
                    'bundle' => $this->_getSelectCategoryField(),
                    'answer_count' => array(
                        '#type' => 'textfield',
                        '#default_value' => isset($values['answer_count']) ? $values['answer_count'] : 3,
                        '#title' => __('Minimum number of answers', 'sabai-discuss'),
                        '#integer' => true,
                    ),
                    'vote_count' => array(
                        '#type' => 'textfield',
                        '#default_value' => isset($values['vote_count']) ? $values['vote_count'] : 10,
                        '#title' => __('Minimum number of votes', 'sabai-discuss'),
                        '#integer' => true,
                    ),
                    'vote_sum' => array(
                        '#type' => 'textfield',
                        '#default_value' => isset($values['vote_sum']) ? $values['vote_sum'] : 5,
                        '#title' => __('Minimum total vote score', 'sabai-discuss'),
                        '#integer' => true,
                    ),
                    'favorite_count' => array(
                        '#type' => 'textfield',
                        '#default_value' => isset($values['favorite_count']) ? $values['favorite_count'] : 3,
                        '#title' => __('Minimum number of favorites', 'sabai-discuss'),
                        '#integer' => true,
                    ),
                    'view_count' => array(
                        '#type' => 'textfield',
                        '#default_value' => isset($values['view_count']) ? $values['view_count'] : 20,
                        '#title' => __('Minimum number of views', 'sabai-discuss'),
                        '#integer' => true,
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of posts to show', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => isset($values['num']) ? $values['num'] : 5, 
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort posts by', 'sabai-discuss'),
                        '#options' => array(
                            'post_published' => __('Date posted', 'sabai-discuss'),
                            'voting_updown.count' => __('Number of votes', 'sabai-discuss'),
                            'voting_updown.sum' => __('Total vote score', 'sabai-discuss'),
                            'content_children_count.value' => __('Number of answers', 'sabai-discuss'),
                            'voting_favorite.count' => __('Number of favorites', 'sabai-discuss'),
                            'post_views' => __('Number of views', 'sabai-discuss'),
                        ),
                        '#default_value' => 'voting_updown.sum', 
                    ),
                );
            case 'answers_accepted':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select add-on', 'sabai-discuss'),
                        '#options' => $options = array('' => __('All add-ons', 'sabai-discuss')) + $this->_addon->getApplication()->Questions_AddonList('answer'),
                        '#type' => count($options) <= 2 ? 'hidden' : 'select',
                        '#default_value' => $this->_addon->getAnswersBundleName(),
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of posts to show', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 5, 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-discuss'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 100, 
                    ),
                );
            case 'tags':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select add-on', 'sabai-discuss'),
                        '#options' => $options = array('' => __('All add-ons', 'sabai-discuss')) + $this->_addon->getApplication()->Questions_AddonList('tag'),
                        '#type' => count($options) <= 2 ? 'hidden' : 'select',
                        '#default_value' => $this->_addon->getTagsBundleName(),
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of tags to show', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 20, 
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort tags by', 'sabai-discuss'),
                        '#options' => array(
                            'term_name' => __('Name', 'sabai-discuss'),
                            'term_created' => __('Date created', 'sabai-discuss'),
                            'taxonomy_content_count.value' => __('Number of posts', 'sabai-discuss'),
                        ),
                        '#default_value' => 'term_name', 
                    )
                );
            case 'categories':
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select add-on', 'sabai-discuss'),
                        '#options' => $options = array('' => __('All add-ons', 'sabai-discuss')) + $this->_addon->getApplication()->Questions_AddonList('category'),
                        '#type' => count($options) <= 2 ? 'hidden' : 'select',
                        '#default_value' => $this->_addon->getCategoriesBundleName(),
                    ),
                    'depth' => array(
                        '#type' => 'textfield',
                        '#title' => __('Category depth (0 for unlimited)', 'sabai-discuss'),
                        '#integer' => true,
                        '#default_value' => 0, 
                    ),
                    'post_count' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show post count', 'sabai-discuss'),
                        '#default_value' => true, 
                    ),
                    'no_posts_hide' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Hide if no posts', 'sabai-discuss'),
                        '#default_value' => false, 
                    ),
                );
            case 'search':
                return array(
                    'addon' => array(
                        '#title' => __('Select add-on', 'sabai-discuss'),
                        '#options' => $options = array('' => __('All add-ons', 'sabai-discuss')) + $this->_addon->getApplication()->Questions_AddonList(),
                        '#type' => count($options) <= 2? 'hidden' : 'select',
                        '#default_value' => $this->_addon->getName(),
                    ),
                );
            case 'askbtn':
                return array(
                    'addon' => array(
                        '#title' => __('Select add-on', 'sabai-discuss'),
                        '#options' => $options = array('' => __('All add-ons', 'sabai-discuss')) + $this->_addon->getApplication()->Questions_AddonList(),
                        '#type' => count($options) <= 2 ? 'hidden' : 'select',
                        '#default_value' => $this->_addon->getName(),
                    ),
                    'label' => array(
                        '#title' => __('Button label', 'sabai-discuss'),
                        '#type' => 'textfield',
                        '#default_value' => __('Publiez la Question', 'sabai-discuss'), 
                    ),
                    'size' => array(
                        '#type' => 'select',
                        '#title' => __('Button size', 'sabai-discuss'),
                        '#options' => array(
                            'xs' => __('Mini', 'sabai-discuss'),
                            'sm' => __('Small', 'sabai-discuss'),
                            '' => __('Medium', 'sabai-discuss'),
                            'lg' => __('Large', 'sabai-discuss'),
                        ),
                        '#default_value' => 'lg', 
                    ),
                    'color' => array(
                        '#type' => 'select',
                        '#title' => __('Button color', 'sabai-discuss'),
                        '#options' => array(
                            'default' => __('White', 'sabai-discuss'),
                            'primary' => __('Blue', 'sabai-discuss'),
                            'info' => __('Light blue', 'sabai-discuss'),
                            'success' => __('Green', 'sabai-discuss'),
                            'warning' => __('Orange', 'sabai-discuss'),
                            'danger' => __('Red', 'sabai-discuss'),
                        ),
                        '#default_value' => 'success', 
                    ),
                );
        }
    }
    
    public function widgetsWidgetGetLabel()
    {
        switch ($this->_name) {
            case 'search':
            case 'askbtn':
                return '';
            default:
                return $this->widgetsWidgetGetTitle();
        }
    }
    
    public function widgetsWidgetGetContent(array $settings)
    {
        if ($this->_name === 'search') {
            return $this->_getSearchForm($settings);
        } elseif ($this->_name === 'askbtn') {
            return $this->_getAskButton($settings);
        } elseif ($this->_name === 'related') {
            return $this->_getRelatedQuestions($settings);
        } elseif ($this->_name === 'leaders') {
            return $this->_getLeaders($settings);
        }

        if (!empty($settings['no_cache'])
            || false === $ret = $this->_addon->getApplication()
                ->getPlatform()
                ->getCache($cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . md5(serialize($settings)))
        ) {             
            if ($this->_name === 'tags') {
                $ret = $this->_getTags($settings);
            } elseif ($this->_name === 'categories') {
                $ret = $this->_getCategories($settings);
            } elseif ($this->_name === 'answers_accepted') {
                $ret = $this->_getAnswers($settings);
            } elseif ($this->_name === 'recent') {            
                $ret = $this->_getPosts($settings);
            } else {            
                $ret = $this->_getQuestions($settings);
            }
            if (empty($settings['no_cache'])) {
                $this->_addon->getApplication()->getPlatform()->setCache($ret, $cache_id, 3600);
            }
        }
        return $ret;
    }
    
    public function widgetsWidgetOnSettingsSaved(array $settings, array $oldSettings)
    {
        // Delete cache
        $cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . md5(serialize($oldSettings));
        $this->_addon->getApplication()->getPlatform()->deleteCache($cache_id);
    }
    
    private function _getLeaders($settings)
    {
        $application = $this->_addon->getApplication();
        $addon = empty($settings['addon']) ? $this->_addon : $application->getAddon($settings['addon']);
        $meta = strtolower($addon->getName()) . '_reputation';
        $ret = array();
        foreach ($application->getPlatform()->getUsersByMeta($meta, $settings['num']) as $user) {
            $ret[] = array(
                'title' => $user->name,
                'url' => '/' . $addon->getSlug('questions') . '/users/' . $user->username, 
                'meta' => array(sprintf(__('%s reputation', 'sabai-discuss'), $user->get($meta))),
                'image' => $application->UserIdentityThumbnailSmall($user),
            );
        }
        return array('content' => $ret);
    }
    
    private function _getPosts($settings)
    {
        $application = $this->_addon->getApplication();
        $addon = empty($settings['addon']) ? $this->_addon : $application->getAddon($settings['addon']);
        $query = $application->Entity_Query('content')
            ->propertyIsIn('post_entity_bundle_name', array($addon->getQuestionsBundleName(), $addon->getAnswersBundleName()))
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->sortByProperty('post_published', 'DESC');
        $ret = array();
        foreach ($query->fetch($settings['num']) as $post) {
            if ($post->getBundleName() === $addon->getAnswersBundleName()) {
                if (!$question = $application->Content_ParentPost($post)) {
                    continue;
                }
                $title = $question->getTitle();
                $date_format = __('answered %s', 'sabai-discuss');
                $url = $application->Entity_Url($post);
            } else {
                $title = $post->getTitle();
                $date_format = __('asked %s', 'sabai-discuss');
                $url = $application->Entity_Url($post);
            }
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $application->Summarize($post->getContent(), $settings['num_chars']) : null,
                'url' => $url,
                'title' => $title,
                'meta' => array('<i class="fa fa-clock-o"></i> ' . sprintf($date_format, $application->getPlatform()->getHumanTimeDiff($post->getTimestamp()))),
                'image' => $application->UserIdentityThumbnailSmall($application->Entity_Author($post)),
            );
        }
        return array('content' => $ret);
    }
    
    private function _getQuestions($settings)
    {
        $application = $this->_addon->getApplication();
        $query = $application->Entity_Query('content')->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if (is_numeric($settings['bundle'])) {
            $category_ids = array($settings['bundle']);
            foreach ($application->Taxonomy_Descendants($settings['bundle'], false) as $_category) {
                $category_ids[] = $_category->id;
            }
            $query->fieldIsIn('questions_categories', $category_ids);
        } else {
            if ($settings['bundle']) {
                $bundle_key = 'post_entity_bundle_name';
                $bundle_value = $settings['bundle'];
            } else {
                $bundle_key =  'post_entity_bundle_type';
                $bundle_value = 'questions';
            }
            $query->propertyIs($bundle_key, $bundle_value);
        }
        switch ($this->_name) {
            case 'featured':
            case 'unanswered':
                $settings += array('num' => 5, 'sort' => 'post_published');
                if ($this->_name === 'featured') {
                    $query->fieldIsNotNull('content_featured');
                } elseif ($this->_name === 'unanswered') {
                    $query->fieldIsNull('questions_resolved');
                }
                if (strpos($settings['sort'], '.')) {
                    list($field, $column) = explode('.', $settings['sort']);
                    $query->sortByField($field, 'DESC', $column);
                } elseif ($settings['sort'] === '_random') {
                    $query->sortByRandom();
                } else {
                    $query->sortByProperty($settings['sort'], 'DESC');
                }
                break;
            
            case 'popular':
                $settings += array('num' => 5, 'sort' => 'voting_updown.sum');
                if (!empty($settings['answer_count'])) {
                    $query->fieldIs('content_children_count', 'questions_answers', 'child_bundle_name')
                        ->fieldIsOrGreaterThan('content_children_count', $settings['answer_count']);
                }
                if (!empty($settings['vote_count'])) {
                    $query->fieldIsOrGreaterThan('voting_updown', $settings['vote_count'], 'count');
                }
                if (!empty($settings['vote_sum'])) {
                    $query->fieldIsOrGreaterThan('voting_updown', $settings['vote_sum'], 'sum');
                }
                if (!empty($settings['favorite_count'])) {
                    $query->fieldIsOrGreaterThan('voting_favorite', $settings['favorite_count'], 'count');
                }
                if (!empty($settings['view_count'])) {
                    $query->propertyIsOrGreaterThan('post_views', $settings['view_count']);
                }
                if (strpos($settings['sort'], '.')) {
                    list($field, $column) = explode('.', $settings['sort']);
                    $query->sortByField($field, 'DESC', $column);
                } else {
                    $query->sortByProperty($settings['sort'], 'DESC');
                }
                break;
        }
        return array('content' => $this->_renderQuestions($query->fetch($settings['num'])));
    }
    
    private function _getRelatedQuestions($settings)
    {
        if (!isset($GLOBALS['sabai_entity'])
            || !$GLOBALS['sabai_entity'] instanceof Sabai_Addon_Content_Entity
            || $GLOBALS['sabai_entity']->getBundleType() !== 'questions'
        ) {
            return;
        }
        
        if (!empty($settings['no_cache'])
            || false === $ret = $this->_addon->getApplication()
                ->getPlatform()
                ->getCache($cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . $GLOBALS['sabai_entity']->getId())
        ) {
            $ret = $this->_doGetRelatedQuestions($GLOBALS['sabai_entity'], $settings);
            if (empty($settings['no_cache'])) {
                $this->_addon->getApplication()->getPlatform()->setCache($ret, $cache_id, 3600);
            }
        }
        return $ret ? array('content' => $ret) : null;
    }
    
    private function _doGetRelatedQuestions($entity, $settings)
    {
        $query = $this->_addon->getApplication()->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $entity->getBundleName())
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIsNot('post_id', $entity->getId());
        $settings += array('num' => 5, 'sort' => 'post_published');
        $tag_ids = $category_ids = array();
        if (!empty($entity->questions_tags)) {
            foreach ($entity->questions_tags as $tag) {
                $tag_ids[] = $tag->getId();
            }
        }
        if (!empty($entity->questions_categories)) {
            foreach ($entity->questions_categories as $category) {
                $category_ids[] = $category->getId();
            }
        }
        if (!empty($tag_ids) && !empty($category_ids)) {
            $query->startCriteriaGroup('OR')
                ->fieldIsIn('questions_tags', $tag_ids)
                ->fieldIsIn('questions_categories', $category_ids)
                ->finishCriteriaGroup();
        } elseif (!empty($tag_ids)) {
            $query->fieldIsIn('questions_tags', $tag_ids);
        } elseif (!empty($category_ids)) {
            $query->fieldIsIn('questions_categories', $category_ids);
        } else {
            $query->fieldIsNull('questions_categories');
        }
        if (strpos($settings['sort'], '.')) {
            list($field, $column) = explode('.', $settings['sort']);
            $query->sortByField($field, 'DESC', $column);
        } elseif ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        return $this->_renderQuestions($query->fetch($settings['num']));
    }
    
    private function _renderQuestions($questions)
    {
        $ret = array();
        $application = $this->_addon->getApplication();
        foreach ($questions as $question) {
            $meta = array(
                '<i class="fa fa-thumbs-up"></i> ' . (int)$question->getSingleFieldValue('voting_updown', 'sum'),
                '<i class="fa fa-comments"></i> ' . (int)$question->getSingleFieldValue('content_children_count', 'questions_answers'),
                '<i class="fa fa-star"></i> ' . (int)$question->getSingleFieldValue('voting_favorite', 'count'),
            );
            $ret[] = array(
                'url' => $application->Entity_Url($question),
                'title' => $question->getTitle(),
                'image' => $application->UserIdentityThumbnailSmall($application->Entity_Author($question)),
                'meta' => $meta,
            );
        }
        return $ret;
    }
    
    private function _getAnswers($settings)
    {
        $settings += array('num' => 5);
        $application = $this->_addon->getApplication();
        $answers = $application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', empty($settings['bundle']) ? $this->_addon->getAnswersBundleName() : $settings['bundle'])
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIsNotNull('questions_answer_accepted', 'score')
            ->sortByField('questions_answer_accepted', 'DESC', 'accepted_at')
            ->fetch($settings['num']);
        $ret = array();
        foreach ($answers as $answer) {
            $question = $application->Content_ParentPost($answer, false);
            if (!$question) continue;

            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $application->Summarize($answer->getContent(), $settings['num_chars']) : null,
                'url' => $application->Entity_Url($answer),
                'title' => $question->getTitle(),
                'meta' => array('<i class="fa fa-clock-o"></i> ' . $application->getPlatform()->getHumanTimeDiff($answer->getSingleFieldValue('questions_answer_accepted', 'accepted_at'))),
                'image' => $application->UserIdentityThumbnailSmall($application->Entity_Author($answer)),
            );
        }
        return array('content' => $ret);
    }
    
    private function _getTags($settings)
    {
        $settings += array('sort' => 'term_name', 'num' => 20);
        $application = $this->_addon->getApplication();
        $bundle = $application->Entity_Bundle(empty($settings['bundle']) ? $this->_addon->getTagsBundleName() : $settings['bundle']);
        $query = $application->Entity_Query('taxonomy')
            ->propertyIs('term_entity_bundle_name', $bundle->name)
            ->fieldIs('taxonomy_content_count', 'questions', 'content_bundle_name')
            ->fieldIsGreaterThan('taxonomy_content_count', 0);
        if (strpos($settings['sort'], '.')) {
            list($field, $column) = explode('.', $settings['sort']);
            $query->sortByField($field, 'DESC', $column);
        } else {
            if ($settings['sort'] === 'term_name') {
                $query->sortByProperty('term_name', 'ASC');
            } else {
                $query->sortByProperty($settings['sort'], 'DESC');
            }
        }
        $ret = array();
        foreach ($query->fetch($settings['num']) as $tag) {
            $ret[] = $application->Questions_TagLink($tag);
        }
        if (empty($ret)) {
            return '';
        }
        return '<ul class="sabai-questions-taglist sabai-questions-taglist-widget"><li>' . implode('</li><li>', $ret) . '</li></ul>';
    }
    
    private function _getCategories($settings)
    {
        return $this->_addon->getApplication()->Taxonomy_HtmlList(
            empty($settings['bundle']) ? $this->_addon->getCategoriesBundleName() : $settings['bundle'],
            array(
                'content_bundle' => 'questions',
                'format' => empty($settings['post_count']) ? '%s' : __('%s (%d)', 'sabai-discuss'),
                'hide_empty' => !empty($settings['no_posts_hide']),
                'depth' => (int)$settings['depth'],
            )
        );
    }
    
    private function _getSearchForm($settings)
    {
        $application = $this->_addon->getApplication();
        $addon = empty($settings['addon']) ? $this->_addon : $application->getAddon($settings['addon']);
        return sprintf(
            '<form method="get" action="%s">
   <input type="text" name="keywords" />
   <input type="submit" value="%s" />
 </form>',
            $application->Url('/'. $addon->getSlug('questions')),
            Sabai::h(__('Search', 'sabai-discuss'))
        );
    }
    
    private function _getAskButton($settings)
    {
        $application = $this->_addon->getApplication();
        $addon = empty($settings['addon']) ? $this->_addon : $application->getAddon($settings['addon']);
        return sprintf(
            '<a href="%s" class="sabai-btn %s %s">%s</a>',
            $application->Url('/'. $addon->getSlug('questions') . '/' . $addon->getSlug('ask')),
            !empty($settings['size']) ? 'sabai-btn-' . $settings['size'] : '',
            !empty($settings['color']) ? 'sabai-btn-' . $settings['color'] : '',
            Sabai::h($settings['label'])
        );
    }
    
    private function _getSelectCategoryField()
    {
        $options = array('' => __('All categories', 'sabai-discuss'));
        $application = $this->_addon->getApplication();
        $sections = $application->Questions_AddonList();
        $single_section = count($sections) === 1;
        foreach ($sections as $addon_name => $title) {
            $addon = $application->getAddon($addon_name);
            $category_bundle = $application->Entity_Bundle($addon->getCategoriesBundleName());
            $tree = $single_section ? array() : array($addon->getQuestionsBundleName() => $title);
            $options += $application->Taxonomy_Tree($category_bundle, array('prefix' => '--', 'init_depth' => 2), $tree);
        }
        
        return array(
            '#title' => __('Select category', 'sabai-discuss'),
            '#options' => $options,
            '#type' => 'select',
            '#default_value' => '',
        );
    }
}