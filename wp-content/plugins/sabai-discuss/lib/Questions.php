<?php
class Sabai_Addon_Questions extends Sabai_Addon
    implements Sabai_Addon_Field_ITypes,
               Sabai_Addon_Taxonomy_ITaxonomies,
               Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Content_IContentTypes,
               Sabai_Addon_Widgets_IWidgets,
               Sabai_Addon_System_IUserMenus,
               Sabai_Addon_System_IAdminMenus,
               Sabai_Addon_System_ISlugs,
               Sabai_Addon_Field_IFilters
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-discuss';
    
    protected $_path, $_allowedAccess, $_questionsBundleName, $_answersBundleName, $_tagsBundleName, $_categoriesBundleName;
    
    protected function _init()
    {
        $this->_path = $this->_application->Path(dirname(__FILE__) . '/Questions');
        $this->_questionsBundleName = $this->_config['questions_name'];
        $this->_answersBundleName = $this->_config['questions_name'] . '_answers';
        $this->_tagsBundleName = $this->_config['questions_name'] . '_tags';
        $this->_categoriesBundleName = $this->_config['questions_name'] . '_categories';
        
        return $this;
    }
    
    public function isCloneable()
    {
        return !$this->hasParent();
    }
    
    public function getQuestionsBundleName()
    {
        return $this->_questionsBundleName;
    }
    
    public function getAnswersBundleName()
    {
        return $this->_answersBundleName;
    }
            
    public function getCategoriesBundleName()
    {
        return $this->_categoriesBundleName;
    }
        
    public function getTagsBundleName()
    {
        return $this->_tagsBundleName;
    }

    public function fieldGetTypeNames()
    {
        return array('questions_resolved', 'questions_closed', 'questions_answer_accepted');
    }

    public function fieldGetType($name)
    {
        require_once $this->_path . '/FieldType.php';
        return new Sabai_Addon_Questions_FieldType($this, $name);
    }
    
    public function systemSlugsGetInfo()
    {
        return array('admin_route' => '/' . strtolower($this->_name) . '/settings/pages', 'admin_weight' => 35);
    }
    
    public function systemGetSlugs()
    {
        $slugs = array(
            'questions' => array(
                'admin_title' => __('Questions Index Page', 'sabai-discuss'),
                'is_root' => true,
                'is_required' => true,
                'title' => $this->_name,
                'slug' => strtolower($this->_name),
            ),
            'ask' => array(
                'admin_title' => __('Ask Question Slug', 'sabai-discuss'),
            ),
            'categories' => array(
                'admin_title' => __('Category Index Page', 'sabai-discuss'),
                'parent' => 'questions',
                'title' => sprintf(__('%s Categories', 'sabai-discuss'), $this->_name),
            ),
            'tags' => array(
                'admin_title' => __('Tag Cloud Page', 'sabai-discuss'),
                'parent' => 'questions',
                'title' => sprintf(__('%s Tags', 'sabai-discuss'), $this->_name),
            ),
            'question' => array(
                'admin_title' => __('Single Question Page', 'sabai-discuss'),
                'parent' => 'questions',
                'title' => sprintf(__('%s Question', 'sabai-discuss'), $this->_name),
            ),
            'flags' => array(
                'admin_title' => __('Flags Page', 'sabai-discuss'),
                'parent' => 'questions',
                'title' => sprintf(__('%s Flags', 'sabai-discuss'), $this->_name),
            ),
            'answers' => array(
                'admin_title' => __('Answers Slug', 'sabai-discuss'),
            ),
            'category' => array(
                'admin_title' => __('Question Category Slug', 'sabai-discuss'),
            ),
            'tag' => array(
                'admin_title' => __('Question Tag Slug', 'sabai-discuss'),
            ),
        );
        
        return $slugs;
    }

    public function systemGetMainRoutes()
    {        
        $routes = array(
            '/' . $this->getSlug('questions') => array(
                'controller' => 'Questions',
                'access_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'posts',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'data' => array(
                    'bundle_name' => $this->_config['questions_name'],
                ),
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/' . $this->getSlug('ask') => array(
                'controller' => 'AskQuestion',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'ask',
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/add' => array(
                'forward' => '/' . $this->getSlug('questions') . '/' . $this->getSlug('ask'),
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/feed' => array(
                'controller' => 'Feed',
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/users/:user_name' => array(
                'controller' => 'UserQuestions',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 1,
                'controller_addon' => 'Questions',
                'priority' => 5,
                'callback_path' => 'user_questions',
                'data' => array('clear_tabs' => true),
                'format' => array(':user_name' => '.+'),
            ),
            '/' . $this->getSlug('questions') . '/users/:user_name/' . $this->getSlug('answers') => array(
                'controller' => 'UserAnswers',
                'access_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'title_callback' => true,
                'weight' => 2,
                'callback_path' => 'user_answers',
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/users/:user_name/favorites' => array(
                'controller' => 'UserFavorites',
                'type' => Sabai::ROUTE_TAB,
                'title_callback' => true,
                'callback_path' => 'user_favorites',
                'weight' => 3,
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/users/:user_name/feed' => array(
                'controller' => 'UserFeed',
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('question') . '/:slug/close' => array(
                'controller' => 'CloseQuestion',
                'access_callback' => true,
                'callback_path' => 'close',
                'title_callback' => true,
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('question') . '/:slug/' . $this->getSlug('answers') => array(
                'controller' => 'QuestionAnswers',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'question_answers',
                'controller_addon' => 'Questions',
                'priority' => 5,
                'weight' => 5,
            ),
            '/' . $this->getSlug('question') . '/:slug/' . $this->getSlug('answers') . '/add' => array(
                'controller' => 'AddAnswer',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'answer',
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('categories') => array(
                'controller' => 'ListHierarchicalTerms',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'categories',
                'controller_addon' => 'Taxonomy',
                'priority' => 5,
                'weight' => 5,
            ),
            '/' . $this->getSlug('categories') . '/:slug/' . $this->getSlug('questions') => array(
                'controller' => 'TermQuestions',
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'weight' => 1,
                'controller_addon' => 'Questions',
                'callback_path' => 'term_questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('tags') => array(
                'controller' => 'Tags',
                'title_callback' => true,
                'access_callback' => true,
                'callback_path' => 'tags',
                'controller_addon' => 'Questions',
                'priority' => 5,
                'weight' => 10,
            ),
            '/' . $this->getSlug('tags') . '/:slug/' . $this->getSlug('questions') => array(
                'controller' => 'TermQuestions',
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'weight' => 1,
                'controller_addon' => 'Questions',
                'callback_path' => 'term_questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/' . $this->getSlug('answers') => array(
                'controller' => 'Answers',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'answers',
                'controller_addon' => 'Questions',
                'priority' => 5,
                'data' => array('clear_tabs' => true),
            ),
            '/' . $this->getSlug('questions') . '/' . $this->getSlug('answers') . '/:entity_id' => array(
                'controller' => 'RedirectToQuestion',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'child_post',
                'priority' => 5,
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/' . $this->getSlug('answers') . '/:entity_id/accept' => array(
                'controller' => 'AcceptAnswer',
                'title_callback' => true,
                'type' => Sabai::ROUTE_CALLBACK,
                'priority' => 5,
                'callback_path' => 'accept',
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/flags' => array(
                'controller' => 'FlaggedPosts',
                'title_callback' => true,
                'callback_path' => 'flags',
                'controller_addon' => 'Questions',
                'priority' => 5,
            ),
            '/' . $this->getSlug('questions') . '/my' => array(
                'forward' => '/' . $this->getSlug('questions') . '/users/:user_name',
                'callback_path' => 'my',
                'access_callback' => true,
            ),
            '/' . $this->getSlug('questions') . '/my/:my_content' => array(
                'forward' => '/' . $this->getSlug('questions') . '/users/:user_name/:my_content',
                'callback_path' => 'my',
                'access_callback' => true,
            ),
        );
        if (!$this->hasParent()) {
            $routes += array(
                '/sabai/questions' => array(
                    'controller' => 'AllQuestions',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'access_callback' => true,
                    'callback_path' => 'questions',
                    'controller_addon' => 'Questions',
                    'priority' => 5,
                ),
                '/sabai/questions' => array(
                    'controller' => 'AllQuestions',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'access_callback' => true,
                    'callback_path' => 'questions',
                    'priority' => 5,
                ),
                '/sabai/questions/answers' => array(
                    'controller' => 'AllAnswers',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/questions/questionlist' => array(
                    'controller' => 'QuestionList',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/questions/favorites' => array(
                    'controller' => 'AllFavorites',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/questions/searchform' => array(
                    'controller' => 'SearchForm',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
            );
        }
        
        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'posts':
                if (!$this->isAllowedAccess()) {
                    $context->setErrorUrl(rtrim($this->_application->getScriptUrl('main'), '/') . '/' . $this->_config['access']['redirect'])
                        ->setFlashEnabled(false); // do not show error message on next access
                    return false;
                }
                $context->config = $this->_config;
                return true;
            case 'ask':
                if ($accessType === Sabai::ROUTE_ACCESS_LINK) {
                    return true;
                }
                
                if ($this->_application->getUser()->isAnonymous()) {
                    if ($this->_application->HasPermission($context->bundle->name . '_add')) {
                        return true;
                    }
                    $path = false !== strpos($route['path'], ':slug') ? str_replace(':slug', $context->entity->getSlug(), $route['path']) : $route['path'];
                    $context->setUnauthorizedError($path);
                    return false;
                }
                return $this->_application->HasPermission($context->bundle->name . '_add');
            case 'close':
                if ($this->_application->HasPermission($this->_config['questions_name'] . '_manage')) {
                    return true;
                }
                if ($this->_application->HasPermission($this->_config['questions_name'] . '_close_any')) {
                    return true;
                }
                return $context->entity->getAuthorId() === $this->_application->getUser()->id
                    && $this->_application->HasPermission($this->_config['questions_name'] . '_close_own');
            case 'answer':
                if ($accessType === Sabai::ROUTE_ACCESS_LINK) {
                    return true;
                }
                if ($this->_application->getUser()->isAnonymous()) {
                    if ($this->_application->HasPermission($context->child_bundle->name . '_add')) {
                        return true;
                    }
                    $context->setUnauthorizedError($this->_application->Entity_Url($context->entity, '/answers/add'));
                    return false;
                }
                return $this->_application->HasPermission($context->child_bundle->name . '_add');
            case 'categories':
                return (!$context->taxonomy_bundle = $this->_application->Entity_Bundle($this->_categoriesBundleName)) ? false : true;
            case 'tags':
                return (!$context->taxonomy_bundle = $this->_application->Entity_Bundle($this->_tagsBundleName)) ? false : true;
            case 'user_questions':
                $user_name = $context->getRequest()->asStr('user_name');
                $context->identity = $this->_application->UserIdentityByUsername(rawurldecode($user_name));
                $context->setTitle(sprintf(__('Posts by %s', 'sabai-discuss'), $context->identity->name));
                return !$context->identity->isAnonymous();
            case 'answers':
            case 'question_answers':
            case 'user_answers':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                return (!$context->child_bundle = $this->_application->Entity_Bundle($this->_answersBundleName)) ? false : true;
            case 'my':
                if ($this->_application->getUser()->isAnonymous()) {
                    return false;
                }
                $route['forward'] = str_replace(':user_name', rawurlencode($this->_application->getUser()->username), $route['forward']);
                return true;
            case 'questions':
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir('sabai-discuss') . '/templates');
                return true;
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'posts':
            case 'term_questions':
                return $titleType === Sabai::ROUTE_TITLE_TAB || $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? _x('Questions', 'tab', 'sabai-discuss')
                    : $this->getTitle('questions');
            case 'ask':
                return __('Ask Question', 'sabai-discuss');
            case 'question_answers':
                return ($count = $context->entity->getSingleFieldValue('content_children_count', 'questions_answers'))
                    ? sprintf(__('Answers (%d)', 'sabai-discuss'), $count)
                    : __('Answers', 'sabai-discuss');
            case 'user_favorites':
                return __('Favorites', 'sabai-discuss');
            case 'categories':
                return $this->_config['label']['categories'];
            case 'tags':
                return $this->_config['label']['tags'];
            case 'flags':
                return __('Flags', 'sabai-discuss');
            case 'close':
                return __('Close Question', 'sabai-discuss');
            case 'user_questions':
                return __('Questions', 'sabai-discuss');
            case 'user_answers':
                return __('Answers', 'sabai-discuss');
            case 'accept':
                return __('Accept Answer', 'sabai-discuss');
            case 'answer':
                return __('Post Answer', 'sabai-discuss');
            case 'answers':
                return __('All Answers', 'sabai-discuss');
        }
    }

    public function systemGetAdminRoutes()
    {
        return array(
            '/' . strtolower($this->_name) . '/settings' => array(
                'controller' => 'Settings',
                'title_callback' => true,
                'controller_addon' => 'Questions',
                'callback_path' => 'settings',
                'data' => array('clear_tabs' => true),
                'weight' => 5,
            ),
            '/' . strtolower($this->_name) . '/settings/search' => array(
                'controller' => 'SearchSettings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Questions',
                'callback_path' => 'settings_search',
                'weight' => 10,
            ),
            '/' . strtolower($this->_name) . '/settings/acl' => array(
                'controller' => 'AccessControl',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Questions',
                'callback_path' => 'settings_acl',
                'weight' => 15,
            ),
            '/' . strtolower($this->_name) . '/settings/emails' => array(
                'controller' => 'Emails',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Questions',
                'callback_path' => 'settings_emails',
                'weight' => 20,
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'settings':
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'settings':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? __('General', 'sabai-discuss') : sprintf(_x('%s Settings', 'Settings page title', 'sabai-discuss'), $this->_name);
            case 'settings_acl':
                return __('Access Control', 'sabai-discuss');
            case 'settings_emails':
                return __('Emails', 'sabai-discuss');
            case 'settings_search':
                return _x('Search', 'settings', 'sabai-discuss');
        }
    }

    public function contentGetContentTypeNames()
    {
        return array($this->_questionsBundleName, $this->_answersBundleName);
    }

    public function contentGetContentType($name)
    {
        require_once $this->_path . '/ContentType.php';
        return new Sabai_Addon_Questions_ContentType($this, $name);
    }   
    
    public function getDefaultConfig()
    {
        return array(
            'front' => array(
                'perpage' => 20,
                'sort' => 'newest',
                'answer_perpage' => 20,
                'answer_sort' => 'votes',
                'feature' => true,
                'categories_tab' => true,
                'tags_tab' => true,
            ),
            'search' => array(
                'no_key' => false,
                'min_keyword_len' => 3,
                'match' => 'all',
                'auto_suggest' => true,
                'suggest_question' => true,
                'suggest_question_jump' => true,
                'suggest_cat' => false,
                'suggest_cat_jump' => false,
                'suggest_cat_icon' => 'folder-open',
                'no_cat' => false,
                'cat_depth' => 2,
                'cat_hide_empty' => false,
                'cat_hide_count' => false,
                'no_filters' => false,
                'filters_top' => false,
                'filters_auto' => true,
                'form_type' => null,
            ),
            'spam' => array(
                'threshold' => 11,
                'auto_delete' => true,
                'delete_after' => 3,
            ),
            'reputation' => array(
                'points' => array(
                    'question_voted' => 5,
                    'question_voted_down' => -2,
                    'answer_voted' => 10,
                    'answer_voted_down' => -2,
                    'answer_accepted' => 15,
                    'answer_accepted_user' => 2,
                    'answer_vote_down' => -1,
                    'spam' => -100,
                    'question_unvoted' => -1,
                    'answer_unvoted' => -1,
                ),
            ),
            'access' => array(
                'type' => 'public',
                'roles' => array(),
                'redirect' => '',
            ),
            'rep_max' => 1000,
            'perm_rep_enable' => false,
            'perm_rep' => array(),
            'questions_name' => strtolower($this->_name),
            'page_title' => array(
                'category' => __('Category: %s', 'sabai-discuss'),
                'tag' => __('Tag: %s', 'sabai-discuss'),
            ),
            'label' => array(
                'categories' => __('Categories', 'sabai-discuss'),
                'category' => __('Category', 'sabai-discuss'),
                'tags' => __('Tags', 'sabai-discuss'),
                'tag' => __('Tag', 'sabai-discuss'),
            ),
        );
    }

    public function widgetsGetWidgetNames()
    {
        if ($this->hasParent()) {
            return array();
        }
        return array('questions_recent', 'questions_popular', 'questions_unanswered', 'questions_categories', 'questions_tags', 'questions_search',
            'questions_answers_accepted', 'questions_askbtn', 'questions_featured', 'questions_related', 'questions_leaders'
        );
    }
    
    public function widgetsGetWidget($name)
    {
        require_once $this->_path . '/Widget.php';
        return new Sabai_Addon_Questions_Widget($this, substr($name, strlen('questions_')));
    }
    
    public function systemGetUserMenus()
    {
        $menus = array();
        if (!$this->isAllowedAccess()
            || !$this->_application->HasPermission(array($this->_config['questions_name'] . '_manage', $this->_answersBundleName . '_manage'))
        ) {
            return $menus;
        }
        
        $all_flag_count = $this->_application->Content_CountFlaggedPosts();
        $flag_count = 0;
        if (isset($all_flag_count[$this->_config['questions_name']])
            && $this->_application->HasPermission($this->_config['questions_name'] . '_manage')
        ) {
            $flag_count += $all_flag_count[$this->_config['questions_name']];
        }
        if (isset($all_flag_count[$this->_answersBundleName])
            && $this->_application->HasPermission($this->_answersBundleName . '_manage')
        ) {
            $flag_count += $all_flag_count[$this->_answersBundleName];
        }
        if ($flag_count > 0) {
            $menus[$this->_config['questions_name']]['title'] = sprintf(__('%s (%d)', 'sabai-discuss'), $this->getTitle('questions'), $flag_count);
            $flags_title = sprintf(__('Flags (%d)', 'sabai-discuss'), $flag_count);
        } else {
            $flags_title = __('Flags', 'sabai-discuss');
        }
        $menus += array(
            $this->_config['questions_name'] . '_flags' => array(
                'title' => $flags_title,
                'url' => $this->_application->MainUrl('/' . $this->getSlug('questions') . '/flags'),
                'parent' => $this->_config['questions_name'],
            ),
        );
        
        return $menus;
    }
    
    public function systemGetAdminMenus()
    {
        $icon_path = str_replace($this->_application->getPlatform()->getSiteUrl() . '/', '', $this->_application->getPlatform()->getAssetsUrl('sabai-discuss'));
        return array(
            '/' . strtolower($this->_name) => array(
                'label' => $this->_name,
                'title' => __('Questions', 'sabai-discuss'),
                'icon' => $icon_path . '/images/icon.png',
                'icon_dark' => $icon_path . '/images/icon_dark.png',
            ),
            '/' . strtolower($this->_name) . '/add' => array(
                'title' => __('Add Question', 'sabai-discuss'),
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/answers' => array(
                'title' => __('Answers', 'sabai-discuss'),
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/categories' => array(
                'title' => $this->_config['label']['categories'],
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/tags' => array(
                'title' => $this->_config['label']['tags'],
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/settings' => array(
                'title' => __('Settings', 'sabai-discuss'),
                'parent' => '/' . strtolower($this->_name),
                'weight' => 99,
            ),
        );
    }

    public function taxonomyGetTaxonomyNames()
    {
        return array($this->_categoriesBundleName, $this->_tagsBundleName);
    }

    public function taxonomyGetTaxonomy($name)
    {
        require_once $this->_path . '/Taxonomy.php';
        return new Sabai_Addon_Questions_Taxonomy($this, $name);
    }
    
    public function isUpgradeable($currentVersion, $newVersion)
    {
        if (!parent::isUpgradeable($currentVersion, $newVersion)) {
            return false;
        }
        if (version_compare($currentVersion, '1.1.0dev322', '<')) {
            $required_addons = array(
                'Content' => '1.1.0dev78',
                'Entity' => '1.1.0dev12',
                'Taxonomy' => '1.1.0',
                'Voting' => '1.0.2dev35'
            );
            return $this->_application->CheckAddonVersion($required_addons);
        }
        
        return true;
    }
            
    public function hasSettingsPage($currentVersion)
    {
        return '/' . strtolower($this->_name) . '/settings';
    }
    
    public function isQuestionTrashable($question, $user)
    {        
        if (!$this->_application->Entity_IsAuthor($question, $user)
            || !$this->_application->HasPermission($this->_config['questions_name'] . '_trash_own', $user->getIdentity())
        ) return false;
        
        // Questions that are resolved or have more than 1 answer are no longer deletable by the question owner 
        if ($question->getSingleFieldValue('questions_resolved')) return false;
        
        $answer_count = (int)$question->getSingleFieldValue('content_children_count', 'questions_answers');
        return $answer_count <= 1;
    }
    
    public function isAnswerTrashable($answer, $user)
    {        
        if (!$this->_application->Entity_IsAuthor($answer, $user)
            || !$this->_application->HasPermission($this->_answersBundleName . '_trash_own', $user->getIdentity())
        ) return false;
        
        // Answers accepted are not longer deletable by the answer owner
        return $answer->getSingleFieldValue('questions_answer_accepted', 'score') ? false : true;
    }
    
    public function onEntityCreateContentQuestionsAnswersEntity($bundle, &$values)
    {
        if ($bundle->name !== $this->_answersBundleName) return;
        
        // Initialize field for this entity
        if (!isset($values['content_post_title'])) {
            // Set the current entity as the parent
            $question_id = $values['content_parent'][0]['value'];
            $question = $this->_application->Entity_Entity('content', $question_id, false);
            $values['content_post_title'] = sprintf(__('Re: %s', 'sabai-discuss'), $question->getTitle());
        }
    }
    
    public function onEntityRenderContentQuestionsHtml($bundle, $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if ($bundle->name !== $this->_questionsBundleName
            || $displayMode === 'preview'
        ) {
            return;
        }
        
        if ($priority = $entity->isFeatured()) {
            $classes[] = 'sabai-questions-featured';
            $classes[] = 'sabai-questions-featured-' . $priority;
        }
        
        $answer_count = (int)@$entity->getSingleFieldValue('content_children_count', 'questions_answers');
        if ($displayMode === 'full') {
            // Add status labels
            if ($entity->getSingleFieldValue('questions_resolved')) {
                $classes[] = 'sabai-questions-accepted';
                $entity->data['entity_labels']['questions_resolved'] = array(
                    'label' => __('Answered', 'sabai-discuss'),
                    'title' => __('This question has been resolved.', 'sabai-discuss'),
                    'icon' => 'check-circle',
                );
            }
            if ($entity->getSingleFieldValue('questions_closed')) {
                $entity->data['entity_labels']['questions_closed'] = array(
                    'label' => __('Closed', 'sabai-discuss'),
                    'title' => __('This question is closed to new answers.', 'sabai-discuss'),
                    'icon' => 'ban',
                );
            }
            
            $user = $this->_application->getUser();
            $can_manage = $this->_application->HasPermission($this->_config['questions_name'] . '_manage');
            if ($can_manage) {
                $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => __('Edit this Question', 'sabai-discuss')));
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/delete', array()), array('width' => 470, 'icon' => 'trash-o'), array('title' => __('Delete this Question', 'sabai-discuss')));
                $links['close'] = $this->_getCloseQuestionLink($entity);
            } else {
                $is_owner = $this->_application->Entity_IsAuthor($entity, $user);
                if ($this->_application->HasPermission($this->_config['questions_name'] . '_edit_any')
                    || ($is_owner && $this->_application->HasPermission($this->_config['questions_name'] . '_edit_own'))
                ) {
                    $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => __('Edit this Question', 'sabai-discuss')));
                }
                if ($this->isQuestionTrashable($entity, $user)) {
                    $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/delete', array()), array('width' => 470, 'icon' => 'trash-o'), array('title' => __('Delete this Question', 'sabai-discuss')));
                }
                if (($is_owner && $this->_application->HasPermission($this->_config['questions_name'] . '_close_own'))
                    || $this->_application->HasPermission($this->_config['questions_name'] . '_close_any')
                ) {
                    $links['close'] = $this->_getCloseQuestionLink($entity);
                }
            }
        } else {
            // Add title icon
            if ($entity->getSingleFieldValue('questions_resolved')) {
                $classes[] = 'sabai-questions-accepted';
                $entity->data['entity_icons']['questions_resolved'] = array(
                    'title' => __('This question has been resolved.', 'sabai-discuss'),
                    'icon' => 'check-circle',
                );
            }
            if ($entity->getSingleFieldValue('questions_closed')) {
                $entity->data['entity_icons']['questions_closed'] = array(
                    'title' => __('This question is closed to new answers.', 'sabai-discuss'),
                    'icon' => 'ban',
                );
            }
        }      
        if (!$entity->getSingleFieldValue('voting_updown', 'count')) {
            $classes[] = 'sabai-questions-novotes';
        }
        if ($answer_count === 0) {
            $classes[] = 'sabai-questions-noanswers';
        }
    }
    
    private function _getCloseQuestionLink($entity)
    {
        if (!$entity->getSingleFieldValue('questions_closed')) {
            $link_label = __('Close', 'sabai-discuss');
            $link_title = __('Close this Question', 'sabai-discuss');
            $active = false;
        } else {
            $link_label = __('Reopen', 'sabai-discuss');
            $link_title = __('Reopen this Question', 'sabai-discuss');            
            $active = true;
        }
        return $this->_application->LinkToModal(
            $link_label,
            $this->_application->Entity_Url($entity, '/close'),
            array(
                'url' => $this->_application->Entity_Url($entity, '/close'),
                'width' => 470,
                'icon' => 'ban',
                'active' => $active,
            ),
            array('title' => $link_title)
        );
    }
    
    public function onEntityRenderContentQuestionsAnswersHtml($bundle, $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if ($bundle->name !== $this->_answersBundleName
            || $displayMode === 'preview'
        ) {
            return;
        }
        
        $answer_score = (int)$entity->getSingleFieldValue('questions_answer_accepted', 'score');
        if ($answer_score) {
            $classes[] = 'sabai-questions-accepted';
        }
        if ($displayMode === 'full') {
            if ($answer_score) {
                if ($answer_score === 3) {
                    $label = __('Best Answer', 'sabai-discuss');
                } elseif ($answer_score === 2) {
                    $label = __('Great Answer', 'sabai-discuss');
                } elseif ($answer_score === 1) {
                    $label = __('Good Answer', 'sabai-discuss');
                }
                $entity->data['entity_labels']['questions_accepted'] = array(
                    'label' => $label,
                    'title' => __('This answer has been accepted.', 'sabai-discuss'),
                    'icon' => 'check-circle',
                );
            }
            $user = $this->_application->getUser();      
            $can_manage = $this->_application->HasPermission($this->_answersBundleName . '_manage');
            if ($can_manage) {
                $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => __('Edit this Answer', 'sabai-discuss')));
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/delete', array('delete_target_id' => $id)), array('width' => 470, 'icon' => 'trash-o'), array('title' => __('Delete this Answer', 'sabai-discuss')));
            } else {
                $is_owner = $this->_application->Entity_IsAuthor($entity, $user);
                if ($this->_application->HasPermission($this->_answersBundleName . '_edit_any')
                    || ($is_owner && $this->_application->HasPermission($this->_answersBundleName . '_edit_own'))
                ) {
                    $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => __('Edit this Answer', 'sabai-discuss')));
                }
                if ($this->isAnswerTrashable($entity, $user)) {
                    $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-discuss'), $this->_application->Entity_Url($entity, '/delete', array('delete_target_id' => $id)), array('width' => 470, 'icon' => 'trash-o'), array('title' => __('Delete this Answer', 'sabai-discuss')));
                }
            }
            $question = $this->_application->Content_ParentPost($entity, true);
            // Question author can accpet answers
            if ($question
                && ($this->_application->Entity_IsAuthor($question, $user)
                    || $this->_application->HasPermission($this->_answersBundleName . '_accept_any'))
            ) {
                $links['accept'] = $this->_application->LinkToModal(
                    $answer_score ? __('Unaccept', 'sabai-discuss') : __('Accept', 'sabai-discuss'),
                    $this->_application->Entity_Url($entity, '/accept', array('update_target_id' => $id)),
                    array('width' => 470, 'icon' => 'check-circle', 'active' => !empty($answer_score)),
                    array('class' => 'sabai-btn-success', 'title' => $answer_score ? __('Unaccept this Answer', 'sabai-discuss') : __('Accept this Answer', 'sabai-discuss'))
                );
            }
        } else {
            if ($answer_score) {
                // Add title icon
                $entity->data['entity_icons']['questions_resolved'] = array(
                    'title' => __('This answer has been accepted.', 'sabai-discuss'),
                    'icon' => 'check-circle',
                );
            }
        }
        if (!$entity->getSingleFieldValue('voting_updown', 'count')) {
            $classes[] = 'sabai-questions-novotes';
        }
    }
    
    public function onVotingContentQuestionsEntityVotedUpdown(Sabai_Addon_Entity_Entity $entity, $results)
    {
        if ($entity->getBundleName() !== $this->_questionsBundleName) return;
        
        $source_user = $this->_application->getUser()->getIdentity();
        $target_user = $this->_application->Entity_Author($entity);
        if ($source_user->id === $target_user->id) return; // no point changes on own content
        
        $source_user_points = $target_user_points = 0;
        $rep_conf = $this->getConfig('reputation');
        
        // Undoing vote?
        if ($results['prev_value'] !== false) {
            $source_user_points += (int)$rep_conf['points']['question_unvoted']; // undoing vote
            if ($results['prev_value'] == 1) {
                $target_user_points -= (int)$rep_conf['points']['question_voted'];
            } elseif ($results['prev_value'] == -1) {
                $target_user_points -= (int)$rep_conf['points']['question_voted_down'];
            }
        }
        // Reflect current vote
        if ($results['value'] == 1) {
            $target_user_points += (int)$rep_conf['points']['question_voted'];           
        } elseif ($results['value'] == -1) {
            $target_user_points += (int)$rep_conf['points']['question_voted_down'];
        }
        
        if ($source_user_points !== 0) {
            $this->_application->Questions_UpdateUserReputation('question_unvoted', $source_user, $source_user_points, $this->_name, array('question' => $entity));
        }
        if ($target_user_points !== 0) {
            $this->_application->Questions_UpdateUserReputation('question_voted', $target_user, $target_user_points, $this->_name, array('question' => $entity));
        }
    }
    
    public function onVotingContentQuestionsAnswersEntityVotedUpdown(Sabai_Addon_Entity_Entity $entity, $results)
    {
        if ($entity->getBundleName() !== $this->_answersBundleName) return;
        
        $source_user = $this->_application->getUser()->getIdentity();
        $target_user = $this->_application->Entity_Author($entity);
        if ($source_user->id === $target_user->id) return; // no point changes on own content
        
        $source_user_points = $target_user_points = 0;
        $rep_conf = $this->getConfig('reputation');
        
        // Undoing vote?
        if ($results['prev_value'] !== false) {
            if ($results['prev_value'] == 1) {
                $source_user_points += (int)$rep_conf['points']['answer_unvoted']; // undoing an upvote
                $target_user_points -= (int)$rep_conf['points']['answer_voted'];
            } elseif ($results['prev_value'] == -1) {
                $target_user_points -= (int)$rep_conf['points']['answer_voted_down'];
            }
        }
        // Reflect current vote
        if ($results['value'] == 1) {
            $target_user_points += (int)$rep_conf['points']['answer_voted'];            
        } elseif ($results['value'] == -1) {
            $source_user_points += (int)$rep_conf['points']['answer_vote_down'];
            $target_user_points += (int)$rep_conf['points']['answer_voted_down'];
        }
        
        if ($source_user_points !== 0) {
            $this->_application->Questions_UpdateUserReputation('answer_unvoted', $source_user, $source_user_points, $this->_name, array('answer' => $entity));
        }
        if ($target_user_points !== 0) {
            $this->_application->Questions_UpdateUserReputation('answer_voted', $target_user, $target_user_points, $this->_name, array('answer' => $entity));
        }
    }    

    public function onEntityCreateContentQuestionsEntitySuccess($bundle, $entity, $values)
    {   
        if ($bundle->name !== $this->_questionsBundleName) {
            return;
        }
        
        if ($entity->isPublished()) {
            $this->_application->Questions_SendQuestionNotification('question_posted', $entity);
        } else {
            $this->_application->Questions_SendQuestionNotification(
                'admin_question_posted',
                $entity,
                null,
                array('{question_url}' => $this->_application->AdminUrl('/' . strtolower($this->_name) . '/' . $entity->getId()))
            );
        }
    }
    
    public function onEntityCreateContentQuestionsAnswersEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_answersBundleName
            || !$question = $this->_application->Content_ParentPost($entity)
        ) {
            return;
        }
        
        if ($entity->isPublished()) {
            $this->_application->Questions_SendAnswerNotification('answer_posted', $entity);
            $this->_notifyQuestionAnswered($entity, $question);
        } else {
            $this->_application->Questions_SendAnswerNotification(
                'admin_answer_posted',
                $entity,
                null,
                array('{answer_url}' => $this->_application->AdminUrl('/' . strtolower($this->_name) . '/' . $this->getSlug('answers') . '/' . $entity->getId()))
            );
        }
    }
    
    public function onContentQuestionsPostPublished($entity)
    {
        if ($entity->getBundleName() !== $this->_questionsBundleName) return;
        
        $this->_application->Questions_SendQuestionNotification('content_published', $entity, null, array(), 'content_');
        $this->_application->Questions_SendQuestionNotification('question_posted', $entity);
    }
    
    public function onContentQuestionsAnswersPostPublished($entity)
    {
        if ($entity->getBundleName() !== $this->_answersBundleName) return;
        
        $this->_application->Questions_SendAnswerNotification('content_published', $entity, null, array(), 'content_');
        $this->_application->Questions_SendAnswerNotification('answer_posted', $entity);
        // Send question answered notification email
        $this->_notifyQuestionAnswered($entity);
    }
    
    public function onQuestionsAnswerAccepted($question, $answer, $score, $timestamp)
    {
        if ($answer->getBundleName() !== $this->_answersBundleName) return;
        
        if ($answer->getAuthorId() === $this->_application->getUser()->id) return;
        
        // Update reputation of source and target users
        $rep_conf = $this->getConfig('reputation', 'points');
        if ($score) {
            $source_user_points = $rep_conf['answer_accepted_user'];
            $target_user_points = $rep_conf['answer_accepted'];
        } else {
            $source_user_points = -1 * $rep_conf['answer_accepted_user'] -1; // undoing acception requires additional point
            $target_user_points = -1 * $rep_conf['answer_accepted'];
        }
        $this->_application->Questions_UpdateUserReputation('accept_answer', $this->_application->getUser()->getIdentity(), $source_user_points, null, array('answer' => $answer));
        $this->_application->Questions_UpdateUserReputation('answer_accepted', $this->_application->Entity_Author($answer), $target_user_points, null, array('answer' => $answer));
        
        if ($score <= 0) return; // answer unaccepted
        
        $this->_application->Questions_SendAnswerNotification('answer_accepted', $answer, null, array('{acceptance_date}' => $this->_application->DateTime($timestamp)));
    }
            
    public function onCommentSubmitCommentSuccess($comment, $isEdit, $entity)
    {
        if ($isEdit
            || $entity->getAuthorId() === $comment->user_id
        ) {
            return;
        }
        
        if ($entity->getBundleName() === $this->_questionsBundleName) {
            $this->_application->Questions_SendQuestionNotification('comment_posted', $entity, null, $this->_application->Comment_TemplateTags($comment), 'content_');
        } elseif ($entity->getBundleName() === $this->_answersBundleName) {
            $this->_application->Questions_SendAnswerNotification('comment_posted', $entity, null, $this->_application->Comment_TemplateTags($comment), 'content_');
        }
    }
        
    private function _notifyQuestionAnswered($answer, $question = null)
    {
        if (!isset($question)
            && (!$question = $this->_application->Content_ParentPost($answer))
        ) {   
            return;
        }
        
        // Send notification if answer author is not the question author
        $question_author = $this->_application->Entity_Author($question);
        if ($question_author->email !== $this->_application->Entity_Author($answer)->email) {
            $this->_application->Questions_SendAnswerNotification('question_answered', $answer, $question_author);
        }
    }
    
    public function onFormBuildContentAdminListposts(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_questionsBundleName) {
            return;
        }
        
        $form['entities']['#header']['answers'] = array(
            'order' => 12,
            'label' => __('Answers', 'sabai-discuss'),
        );      
        foreach ($form['entities']['#options'] as $entity_id => $data) {
            $entity = $data['#entity'];
            $icons = array();
            if ($entity->getSingleFieldValue('questions_resolved')) {
                $icons[] = '<i class="fa fa-check-circle sabai-entity-icon-questions-accepted"></i>';
            }
            if ($entity->getSingleFieldValue('questions_closed')) {
                $icons[] = '<i class="fa fa-ban sabai-entity-icon-questions-closed"></i>';
            }
            if ($entity->isFeatured()) {
                $icons[] = '<i class="fa fa-certificate sabai-entity-icon-featured"></i>';
            }
            $form['entities']['#options'][$entity_id]['title'] = '<span class="sabai-questions-icons">' . implode(PHP_EOL, $icons) . '</span> ' . $form['entities']['#options'][$entity_id]['title'];
            $form['entities']['#options'][$entity_id] += array(
                'answers' => $this->_application->LinkTo(
                    (int)$entity->getSingleFieldValue('content_children_count', 'questions_answers'),
                    $this->_application->Url('/' . strtolower($this->_name) . '/' . $this->getSlug('answers'), array('content_parent' => $entity_id))
                ),
            );
        }
        $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options'] += array(
            'close' => __('Close', 'sabai-discuss'),
            'reopen' => __('Reopen', 'sabai-discuss'),
        );
        $form['#submit'][0][] = array($this, 'updateQuestions');
        $this->_addAdminListPostsFormHeader($form);
            
        $form['#filters']['questions_resolved'] = array(
            'order' => 5,
            'default_option_label' => sprintf(__('Resolved/Unresolved', 'sabai-discuss')),
            'options' => array(1 => __('Resolved', 'sabai-discuss'), 2 => __('Unresolved', 'sabai-discuss')),
        );
        $form['#filters']['questions_featured'] = array(
            'order' => 6,
            'default_option_label' => sprintf(__('Featured/Unfeatured', 'sabai-discuss')),
            'options' => array(1 => __('Featured', 'sabai-discuss'), 2 => __('Unfeatured', 'sabai-discuss')),
        );
        $form['#filters']['questions_closed'] = array(
            'order' => 7,
            'default_option_label' => sprintf(__('Open/Closed', 'sabai-discuss')),
            'options' => array(2 => __('Open', 'sabai-discuss'), 1 => __('Closed', 'sabai-discuss')),
        );
    }
    
    public function onContentAdminPostsUrlParamsFilter(&$urlParams, $context, $bundle)
    {
        if ($bundle->name === $this->_questionsBundleName) {
            foreach (array('questions_resolved', 'questions_featured', 'questions_closed') as $key) {
                if ($value = $context->getRequest()->asInt($key)){
                    $urlParams[$key] = $value;
                }
            }
        } elseif ($bundle->name === $this->_answersBundleName) {
            if ($questions_accepted = $context->getRequest()->asInt('questions_accepted')){
                $urlParams['questions_accepted'] = $questions_accepted;
            }
        }
    }
    
    public function onContentAdminPostsQuery($context, $bundle, $query, $countQuery, $sort, $order)
    {
        if ($bundle->name === $this->_questionsBundleName) {
            foreach (array('questions_resolved' => 'questions_resolved', 'questions_featured' => 'content_featured', 'questions_closed' => 'questions_closed') as $key => $field) {
                if ($value = $context->getRequest()->asInt($key)){
                    switch ($value) {
                        case 1:
                            $query->fieldIs($field, 1);
                            $countQuery->fieldIs($field, 1);
                        break;
                        case 2:
                            $query->fieldIsNull($field);
                            $countQuery->fieldIsNull($field);
                        break;
                    }
                }
            }
        } elseif ($bundle->name === $this->_answersBundleName) {
            if ($questions_accepted = $context->getRequest()->asInt('questions_accepted')){
                switch ($questions_accepted) {
                    case 1:
                        $query->fieldIsOrGreaterThan('questions_answer_accepted', 1, 'score');
                        $countQuery->fieldIsOrGreaterThan('questions_answer_accepted', 1, 'score');
                    break;
                    case 2:
                        $query->fieldIsNull('questions_answer_accepted', 'score');
                        $countQuery->fieldIsNull('questions_answer_accepted', 'score');
                    break;
                }
            }
        }
    }
    
    public function onFormBuildContentAdminListchildposts(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_answersBundleName) {
            return;
        }
        $form['entities']['#header']['title'] = array(
            'label' => __('Answer', 'sabai-discuss'),
            'order' => 1,
        );
        foreach ($form['entities']['#options'] as $entity_id => $data) {
            $entity = $data['#entity'];
            $icon = $entity->getSingleFieldValue('questions_answer_accepted', 'score') ? '<i class="fa fa-check-circle sabai-entity-icon-questions-accepted"></i> ' : '';
            $form['entities']['#options'][$entity_id]['title'] = $icon . $this->_application->Summarize($entity->getContent(), 200) . '<div class="sabai-row-action">' . $this->_application->Menu($data['#links']) . '</div>';
        }
        $this->_addAdminListPostsFormHeader($form);
        
        $form['#filters']['questions_accepted'] = array(
            'order' => 5,
            'default_option_label' => sprintf(__('Show accepted/unaccepted', 'sabai-discuss')),
            'options' => array(1 => __('Show accepted', 'sabai-discuss'), 2 => __('Show unaccepted', 'sabai-discuss')),
        );
    }
    
    private function _addAdminListPostsFormHeader(&$form)
    {
        if ($form['#status'] === 'trashed') {
            $form['#header'] = array(
                '<p>' . sprintf(__('Posts <span class="sabai-tr-error">highlighted</span> are marked as SPAM. Authors of these posts will lose %d reputation points when the posts are removed from trash.', 'sabai-discuss'), abs($this->_config['reputation']['points']['spam'])) . '</p>'
            );
        }
    }
    
    public function updateQuestions(Sabai_Addon_Form_Form $form)
    {
        if (!empty($form->values['entities'])) {
            switch ($form->values['action']) {
                case 'close':
                    $this->_closeQuestions($form->values['entities']);
                    break;
                case 'reopen':
                    $this->_closeQuestions($form->values['entities'], false);
                    break;
            }
        }
    }
    
    protected function _closeQuestions($entities, $close = true)
    {
        foreach ($this->_application->Entity_TypeImpl('content')->entityTypeGetEntitiesByIds($entities) as $entity) {
            $this->_application->Entity_Save($entity, array('questions_closed' => $close));
        }
    }
    
    public function onSystemLoadPermissions($identity, &$permissions)
    {
        if (!$this->_config['perm_rep_enable'] || $identity->isAnonymous()) return;
        
        $user_roles = $this->_application->getPlatform()->getUserRolesByUser($identity->id);
        $user_reputation = $this->_application->Questions_UserReputation($identity, $this->_name);
        foreach ($user_roles as $user_role) {
            if (!isset($this->_config['perm_rep'][$user_role])) continue;
            // Add permissions that have minimum reputation lower or equal to the current user reputation
            foreach ($this->_config['perm_rep'][$user_role] as $permission => $reputation) {
                if ($user_reputation >= $reputation) {
                    $permissions[$permission] = 1;
                } else {
                    unset($permissions[$permission]);
                }
            }
        }
    }
    
    public function onEntityBulkDeleteContentQuestionsEntitySuccess($bundle, $entities, $extra)
    {
        if ($bundle->name !== $this->_questionsBundleName) return;
        
        $this->_onEntityBulkDeleteContentEntitySuccess($bundle, $entities, $extra);
    }
    
    public function onEntityBulkDeleteContentQuestionsAnswersEntitySuccess($bundle, $entities, $extra)
    {
        if ($bundle->name !== $this->_answersBundleName) return;
        
        $this->_onEntityBulkDeleteContentEntitySuccess($bundle, $entities, $extra);
    }
        
    private function _onEntityBulkDeleteContentEntitySuccess($bundle, $entities, $extra)
    {
        // Subtract reputation points from authors of spam posts
        foreach ($entities as $post) {
            if ($post->getSingleFieldValue('content_trashed', 'type') === Sabai_Addon_Content::TRASH_TYPE_SPAM) {
                $this->_application->Questions_UpdateUserReputation('content_deleted', $this->_application->Entity_Author($post), $this->_config['reputation']['points']['spam'], $this->_name, array('content' => $post));
            }
        }
    }
    
    public function onQuestionsInstallSuccess($addon)
    {
        if ($addon->getName() !== $this->_name) return;
        
        $this->_application->Questions_CreateSampleData($addon->getName());
    }
    
    public function onVotingContentQuestionsEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_questionsBundleName) return;
        
        $this->_application->Questions_SendQuestionNotification('question_flagged', $entity, null, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    public function onVotingContentQuestionsAnswersEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_answersBundleName) return;
        
        $this->_application->Questions_SendAnswerNotification('answer_flagged', $entity, null, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    private function _trashPostIfSpam(Sabai_Addon_Entity_IEntity $entity, $results)
    {
        if ($entity->isTrashed()) return; // trashed posts can not be flagged, but just in case

        // Has the spam score reached the threshold?
        if ($results['sum'] > $this->_config['spam']['threshold'] + 0.3 * (int)$entity->getSingleFieldValue('voting_updown', 'sum')) {
            // Move to trash and clear flags
            $this->_application->Content_TrashPosts($entity, Sabai_Addon_Content::TRASH_TYPE_SPAM, '', 0);
        }
    }
    
    public function onSabaiRunCron($lastRunTimestamp, $logs)
    {
        if (!$this->_config['spam']['auto_delete']) {
            // Auto-delete spam not enabled
            return;
        }
        
        // Fetch posts marked as spam and were trashed more than X days ago
        $days = $this->_config['spam']['delete_after'];
        $spam_posts = $this->_application->Entity_Query('content')
            ->propertyIsIn('post_entity_bundle_name', array($this->_questionsBundleName, $this->_answersBundleName))
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_TRASHED) // trashed posts
            ->fieldIs('content_trashed', Sabai_Addon_Content::TRASH_TYPE_SPAM, 'type') // marked as spam
            ->fieldIsOrSmallerThan('content_trashed', time() - $days * 86400, 'trashed_at') // more than X days after trashed
            ->fetch(0, 0, false);    
        if (empty($spam_posts)) {
            return;
        }
        // Delete
        $this->_application->Content_DeletePosts($spam_posts);
        $logs[] = sprintf(
            __('Deleted %d spam posts (questions and/or answers) from trash', 'sabai-discuss'),
            count($spam_posts)
        );
    }
    
    public function isAllowedAccess()
    {
        if (!isset($this->_allowedAccess)) {
            $this->_allowedAccess = $this->_checkAllowedAccess();
        }
        return $this->_allowedAccess;
    }
    
    private function _checkAllowedAccess()
    {
        if ($this->_config['access']['type'] === 'closed') {
            return false;
        }
        if ($this->_config['access']['type'] === 'public'
            || $this->_application->getUser()->isAdministrator()
        ) {
            return true;
        }
        // Deny non-registered users
        if ($this->_application->getUser()->isAnonymous()) {
            return false;
        }
        // Allow selected roles only?
        if (!empty($this->_config['access']['roles'])) {
            foreach ($this->_application->getPlatform()->getUserRolesByUser($this->_application->getUser()->getIdentity()->id) as $role) {
                if (in_array($role, $this->_config['access']['roles'])) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function onQuestionsUpgradeSuccess(Sabai_Addon $addon, $log, $previousVersion)
    {
        if ($addon->getName() !== $this->_name
            || $this->hasParent() // Cloned add-ons should not proceed
        ) {
            return;
        }
        require_once $this->_path . '/includes/upgrade.php';
        sabai_addon_questions_upgrade($this->_application, $previousVersion);
    }
    
    public function onEntityTitleFilter(&$title, $entity)
    {
        switch ($entity->getBundleName()) {
            case $this->_categoriesBundleName:
                if (isset($this->_config['page_title']['category'])) {
                    $title = sprintf($this->_config['page_title']['category'], $title);
                }
                break;
            case $this->_tagsBundleName:
                if (isset($this->_config['page_title']['tag'])) {
                    $title = sprintf($this->_config['page_title']['tag'], $title);
                }
                break;
        }
    }
    
    public function onEntityBundleLabelFilter(&$label, $bundleName, $singular)
    {
        if ($bundleName === $this->_categoriesBundleName) {
            $key = $singular ? 'category' : 'categories';
            if (!empty($this->_config['label'][$key])) {
                $label = $this->_config['label'][$key];
            }
        } elseif ($bundleName === $this->_tagsBundleName) {
            $key = $singular ? 'tag' : 'tags';
            if (!empty($this->_config['label'][$key])) {
                $label = $this->_config['label'][$key];
            }
        }
    }
    
    public function onSystemUserActivityFilter(&$activity, $identity, $counts)
    {
        if (!$this->isAllowedAccess()) {
            return;
        }
        $reputation = $this->_application->Questions_UserReputation($identity, $this->_name);
        if ($reputation || isset($counts[$this->_questionsBundleName]) || isset($counts[$this->_answersBundleName])) {
            $_activity = array(            
                'stats' => array(
                    $this->_questionsBundleName => array(
                        'url' => '/' . $this->getSlug('questions') . '/users/' . $identity->username,
                        'format' => _n('%s question', '%s questions', $count = isset($counts[$this->_questionsBundleName]) ? $counts[$this->_questionsBundleName] : 0, 'sabai-discuss'),
                        'count' => $count,
                        'type' => 'questions',
                    ),
                    $this->_answersBundleName => array(
                        'url' => '/' . $this->getSlug('questions') . '/users/' . $identity->username . '/' . $this->getSlug('answers'),
                        'format' => _n('%s answer', '%s answers', $count = isset($counts[$this->_answersBundleName]) ? $counts[$this->_answersBundleName] : 0, 'sabai-discuss'),
                        'count' => $count,
                        'type' => 'questions_answers',
                    ),
                    'reputation' => array(
                        'format' => __('%s reputation', 'sabai-discuss'),
                        'count' => $this->_application->Questions_UserReputation($identity, $this->_name),
                    ),
                ),
                'title' => $this->getTitle('questions'),
            );
            $activity[$this->_name] = $this->_application->Filter('questions_user_profile_activity', $_activity, array($identity, $this->_name));
        }
    }
    
    public function onSabaiWebResponseRenderHtmlLayout(Sabai_Context $context, &$content)
    {
        if ($this->hasParent()) return;

        if ($this->_application->getPlatform()->isAdmin()) {
            $this->_application->LoadCss('admin.min.css', 'sabai-discuss', 'sabai', 'sabai-discuss');
        } else {
            $this->_application->LoadCss('main.min.css', 'sabai-discuss', 'sabai', 'sabai-discuss');
            if ($this->_application->getPlatform()->isLanguageRTL()) {
                $this->_application->LoadCss('main-rtl.min.css', 'sabai-discuss-rtl', 'sabai-discuss', 'sabai-discuss');
            }
        }
    }
    
    public function onSystemEmailSettingsFilter(&$settings, $addonName)
    {
        if ($this->_application->getAddon($addonName)->getType() !== 'Questions') return;
        
        $settings += $this->_application->Questions_NotificationSettings();
        
        // For backward compat with 1.1.8 or lower versions
        if (isset($this->_config['emails'])) {
            foreach ($this->_config['emails'] as $name => $_settings) {
                if (isset($_settings['enable'])) {
                    $settings[$name]['enable'] = !empty($_settings['enable']);
                }
                if (isset($_settings['roles'])) {
                    $settings[$name]['roles'] = $_settings['roles'];
                }
                if (isset($_settings['email']['subject'])) {
                    $settings[$name]['email']['subject'] = $_settings['email']['subject'];
                }
                if (isset($_settings['email']['body'])) {
                    $settings[$name]['email']['body'] = $_settings['email']['body'];
                }
            }
        }
    }
    
    public function fieldGetFilterNames()
    {
        return array('questions_resolved', 'questions_open', 'questions_answer_accepted');
    }

    public function fieldGetFilter($name)
    {
        switch ($name) {
            case 'questions_resolved':
                require_once $this->_path . '/ResolvedFieldFilter.php';
                return new Sabai_Addon_Questions_ResolvedFieldFilter($this, $name);
            case 'questions_open':
                require_once $this->_path . '/OpenFieldFilter.php';
                return new Sabai_Addon_Questions_OpenFieldFilter($this, $name);
            case 'questions_answer_accepted':
                require_once $this->_path . '/AnswerAcceptedFieldFilter.php';
                return new Sabai_Addon_Questions_AnswerAcceptedFieldFilter($this, $name);
        }
    }
    
    public function onFieldUIFieldViewsFilter(&$views, $fieldType, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        switch ($bundle->type) {
            case 'questions':
                $views += array(
                    'default' => __('Detailed view', 'sabai-discuss'),
                    'summary' => array('title' => __('Summary view', 'sabai-discuss'), 'inherit' => true, 'display' => false),
                );
                break;
            case 'questions_answers':
                $views += array(
                    'default' => __('Detailed view', 'sabai-discuss'),
                );
                break;
            case 'questions_tags':
            case 'questions_categories':
                if ($fieldType !== 'taxonomy_term_title') {
                    $views += array(
                        'default' => __('Detailed view', 'sabai-discuss'),
                    );
                }
                break;
        }
    }

    public function onFormBuildFielduiAdminCreateField(&$form, &$storage)
    {
        if ($form['#bundle'] !== $this->_questionsBundleName || !isset($storage['field_widget'])) return;
        
        $this->_onFormBuildFielduiAdminField($form, $storage);
    }
    
    public function onFormBuildFielduiAdminEditField(&$form, &$storage)
    {
        if ($form['#bundle'] !== $this->_questionsBundleName || !$form['#field']->isCustomField()) return;
        
        $this->_onFormBuildFielduiAdminField($form, $storage, $form['#field']->getFieldData('questions_categories'));
    }
    
    public function _onFormBuildFielduiAdminField(&$form, &$storage, array $categoryDefault = null)
    {
        $depth = $this->_application->getModel(null, 'Taxonomy')->getGateway('Term')->getMaxDepth($this->_categoriesBundleName);
        $form['basic']['questions_categories'] = array(
            '#title' => __('Categories', 'sabai-discuss'),
            '#description' => __('Select categories to which the field belongs. The field will then be enabled only when one or more of the following categories are selected.', 'sabai-discuss'),
            '#weight' => 99,
            '#collapsible' => false,
            '#tree' => true,
            '#class' => 'sabai-form-group',
            '#element_validate' => array(array($this, 'validateTaxonomySelect')),
        );
        $next_index = 1;
        if (!empty($categoryDefault)) {
            foreach ($categoryDefault as $category_id) {
                $form['basic']['questions_categories'][] = $this->_getSelectCategoryField($depth, $category_id);
                ++$next_index;
            }
        }
        $form['basic']['questions_categories'][] = $this->_getSelectCategoryField($depth);
        $form['basic']['questions_categories']['_add'] = array(
            '#type' => 'item',
            '#markup' => sprintf(
                '<a href="#" class="sabai-btn sabai-btn-default sabai-btn-xs sabai-form-field-add" data-field-name="questions_categories" data-field-next-index="%d"><i class="fa fa-plus"></i> %s</a>',
                $next_index,
                __('Add More', 'sabai-discuss')
            ),
            '#class' => 'sabai-form-field-add',
        );
    }
    
    protected function _getSelectCategoryField($depth, $value = null)
    {
        $default_text = sprintf(__('Select %s', 'sabai-discuss'), $this->_application->Entity_BundleLabel($this->_categoriesBundleName, true));
        $ret = array(
            '#type' => 'select',
            '#empty_value' => '',
            '#default_value' => $value,
            '#multiple' => false,
            '#options' => $this->_getTermList($default_text),
        );
        if ($depth) {
            if (isset($value) && !isset($ret['#options'][$value])) {
                foreach ($this->_application->getModel('Term', 'Taxonomy')->fetchParents($value) as $parent) {
                    $default_values[] = $parent->id;
                }
                $default_values[] = $value;
                $ret['#default_value'] = $default_values[0];
            }
            $ret = array(
                0 => array('#weight' => 0, '#class' => 'sabai-taxonomy-term-0') + $ret,
                '#class' => 'sabai-form-inline',
            );
            $url = $this->_application->MainUrl('/sabai/taxonomy/child_terms', array('bundle' => $this->_categoriesBundleName, Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');
            for ($i = 1; $i <= $depth; $i++) {
                $ret[$i] = array(
                    '#type' => 'select',
                    '#class' => 'sabai-hidden sabai-taxonomy-term-' . $i,
                    '#attributes' => array('data-load-url' => $url),
                    '#states' => array(
                        'load_options' => array(
                            sprintf('.sabai-taxonomy-term-%d select', $i - 1) => array('type' => 'selected', 'value' => true, 'container' => '.sabai-form-fields'),
                        ),
                    ),
                    '#options' => array('' => $default_text),
                    '#states_selector' => '.sabai-taxonomy-term-' . $i,
                    '#skip_validate_option' => true,
                    '#weight' => $i,
                    '#default_value' => isset($default_values[$i]) ? $default_values[$i] : null,
                    '#field_prefix' => $this->_application->getPlatform()->isLanguageRTL() ? '&nbsp;&laquo;' : '&nbsp;&raquo;',
                );
            }
        }
        return $ret;
    }
    
    protected function _getTermList($defaulText = '', $parent = 0)
    {
        $ret = array('' => $defaulText);
        $terms = $this->_application->Taxonomy_Terms($this->_categoriesBundleName);
        if (!empty($terms[$parent])) {
            foreach ($terms[$parent] as $term) {
                $ret[$term['id']] = $term['title']; 
            }
        }
        return $ret;
    }
    
    public function validateTaxonomySelect(Sabai_Addon_Form_Form $form, &$value, $element)
    {
        unset($value['_add']);
        $new_value = array();
        foreach ($value as $_value) {
            if (!is_array($_value)) {
                if (is_numeric($_value)) $new_value[] = $_value; // for sites with single level categories
                continue;
            }
            while (null !== $__value = array_pop($_value)) {
                if ($__value !== '') {
                    $new_value[] = $__value;
                    break;
                }
            }
        }
        $value = $new_value;
    }
    
    public function onFieldUIFieldDataFilter(&$fieldData, $bundle, $fieldName, $values)
    {
        $fieldData['data']['questions_categories'] = array();
        
        if (!empty($values['questions_categories'])) {
            foreach (array_unique(array_filter($values['questions_categories'])) as $category_id) {
                $fieldData['data']['questions_categories'][] = $category_id;
            }
        }
    }
    
    public function onFormBuildEntityForm(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_questionsBundleName) return;
        
        foreach ($form['#fields'] as $field_name => $field) {
            if (!$category_ids = $field->getFieldData('questions_categories')) {
                continue;
            }
            if (!isset($form['questions_categories'])) {
                // category field is disabled
                unset($form[$field_name]);
                continue;
            }
            $form[$field_name]['#states']['visible']['.sabai-entity-field-name-questions-categories select'] = array(
                'type' => 'value', // match one
                'value' => $category_ids,
            );
            if (isset($form[$field_name][0])) {
                if (!empty($form[$field_name][0]['#required'])) {
                    $form[$field_name][0]['#required'] = array(array(__CLASS__, 'isFieldRequired'), array($category_ids));
                }
            } else {
                if (!empty($form[$field_name]['#required'])) {
                    $form[$field_name]['#required'] = array(array(__CLASS__, 'isFieldRequired'), array($category_ids));
                }
            }
        }
    }
    
    public static function isFieldRequired($form, $categoryIds)
    {
        foreach ($form->values['questions_categories'] as $cateogry_id) {
            if (in_array($cateogry_id, $categoryIds)) return true;
        }
        return false;
    }
    
    public function onEntityFilterFormFiltersFilter(&$filters, $bundle)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        if (!empty($_REQUEST['category'])) {
            $category_id = $_REQUEST['category'];
        } elseif (isset($GLOBALS['sabai_entity']) && $GLOBALS['sabai_entity']->getBundleName() === $this->_categoriesBundleName) {
            $category_id = $GLOBALS['sabai_entity']->getId();
        }

        foreach ($filters as $filter_name => $filter) {
            if (($category_ids = $filter['#filter']->Field->getFieldData('questions_categories'))
                && (!isset($category_id) || !in_array($category_id, $category_ids))
            ) {
                // Hide the field but let the form element process so that required js scripts are loaded on page load
                $filters[$filter_name]['#template'] = '';
            }
        }
    }
}