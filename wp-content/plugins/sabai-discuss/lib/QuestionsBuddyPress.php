<?php
class Sabai_Addon_QuestionsBuddyPress extends Sabai_Addon
    implements Sabai_Addon_System_IAdminSettings
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-discuss';
    
    public function isUninstallable($currentVersion)
    {
        return true;
    }
    
    public function getDefaultConfig()
    {
        return array(
            'nav_name' => $this->_application->getAddon('Questions')->getTitle('questions'),
            'nav_slug' => 'questions',
            'nav_position' => 21,
            'activities' => array('questions', 'answers', 'comments'),
        );
    }
    
    public function onSabaiPlatformWordpressInit()
    {
        add_action('bp_setup_nav', array($this, 'bpSetupNavAction'), 10);
        add_filter('bp_activity_can_comment', array($this, 'bpActivityCanCommentFilter'), 10, 2);
    }
    
    public function bpActivityCanCommentFilter($canComment, $activityType)
    {
        return $canComment
            && !in_array($activityType, array('new_questions', 'new_questions_answers', 'new_comment'));
    }
    
    public function bpSetupNavAction()
    {
        // Determine user to use
        if (bp_displayed_user_domain()) {
            $user_domain = bp_displayed_user_domain();
        } elseif (bp_loggedin_user_domain()) {
            $user_domain = bp_loggedin_user_domain();
        } else {
            return;
        }
        // Add main menu item
        bp_core_new_nav_item(array(
            'name' => $this->_config['nav_name'],
            'slug' => $this->_config['nav_slug'],
            'position' => $this->_config['nav_position'],
            'default_subnav_slug' => 'questions',
        ));
        // Add sub menu items
        $navs = array(
            'questions' => __('Questions', 'sabai-discuss'),
            'answers' => __('Answers', 'sabai-discuss'),
            'favorites' => __('Favorites', 'sabai-discuss'),
        );
        $parent_url = $user_domain . $this->_config['nav_slug'] . '/';
        $position = 0;
        foreach ($navs as $slug => $name) {
            bp_core_new_subnav_item(array(
                'name' => $name,
                'slug' => $slug,
                'parent_url' => $parent_url,
                'parent_slug' => $this->_config['nav_slug'],
                'screen_function' => array($this, 'bpNav' . $slug .'ScreenFunction'),
                'position' => $position + 10,
                'user_has_access' => true,
            ));
        }
    }
    
    private function _bpNavScreenFunction($name)
    {
        add_action('bp_template_content', array($this, 'bpNav' . $name . 'Content'));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }
    
    public function bpNavQuestionsScreenFunction()
    {
        $this->_bpNavScreenFunction('Questions');
    }
    
    public function bpNavQuestionsContent()
    {
        echo do_shortcode('[sabai-discuss return=1 hide_searchbox=1 user_id=' . bp_displayed_user_id() . ']');
    }
    
    public function bpNavAnswersScreenFunction()
    {
        $this->_bpNavScreenFunction('Answers');
    }
    
    public function bpNavAnswersContent()
    {
        echo do_shortcode('[sabai-discuss-answers return=1 user_id=' . bp_displayed_user_id() . ']');
    }
 
    public function bpNavFavoritesScreenFunction()
    {
        $this->_bpNavScreenFunction('Favorites');
    }
    
    public function bpNavFavoritesContent()
    {
        echo do_shortcode('[sabai-discuss-favorites return=1 user_id=' . bp_displayed_user_id() . ']');
    }
    
    public function onEntityCreateContentQuestionsEntitySuccess($bundle, $entity, $values)
    {   
        if (!function_exists('bp_activity_add')
            || !in_array('questions', $this->_config['activities'])
        ) return;
        
        $addon = $this->_application->Entity_Addon($entity);
        bp_activity_add(array(
            'user_id' => $entity->getAuthorId(),
            'action' => sprintf(
                __('%s posted a new question to %s', 'sabai-discuss'),
                bp_core_get_userlink($entity->getAuthorId()),
                '<a href="' . $this->_application->Url('/' . $addon->getSlug('questions')) . '">' . $addon->getTitle('questions') . '</a>'
            ),
            'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                $permalink = $this->_application->Entity_Permalink($entity),
                $this->_application->Summarize($entity->getContent(), 200)
            )),
            'primary_link' => $permalink,
            'type' => 'new_' . $bundle->type,
            'item_id' => $entity->getId(),
            'secondary_item_id' => false,
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-discuss',
        ));
    }
    
    public function onEntityCreateContentQuestionsAnswersEntitySuccess($bundle, $entity, $values)
    {
        if (!function_exists('bp_activity_add')
            || !in_array('answers', $this->_config['activities'])
        ) return;
        
        if (!$question = $this->_application->Content_ParentPost($entity, false)) return;
        
        bp_activity_add(array(
            'user_id' => $entity->getAuthorId(),
            'action' => sprintf(
                __('%s answered to question %s', 'sabai-discuss'),
                bp_core_get_userlink($entity->getAuthorId()),
                $this->_application->Entity_Permalink($question)
            ),
            'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                $permalink = $this->_application->Entity_Permalink($entity), 
                $this->_application->Summarize($entity->getContent(), 200)
            )),
            'primary_link' => $permalink,
            'type' => 'new_' . $bundle->type,
            'item_id' => $question->getId(),
            'secondary_item_id' => $entity->getId(),
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-discuss',
        ));
    }
    
    public function onCommentSubmitCommentSuccess($comment, $isEdit, $entity)
    {
        if ($isEdit
            || !function_exists('bp_activity_add')
            || !in_array('comments', $this->_config['activities'])
        ) return;
        
        switch ($entity->getBundleType()) {
            case 'questions':
                $action = __('%1$s commented on a <a href="%2$s">question</a>', 'sabai-discuss');
                break;
            case 'questions_answers':
                $action = __('%1$s commented on an <a href="%2$s">answer</a>', 'sabai-discuss');
                break;
            default:
                return;
        }
        
        bp_activity_add(array(
            'user_id' => $comment->user_id,
            'action' => sprintf(
                $action,
                bp_core_get_userlink($comment->user_id),
                $this->_application->Entity_Url($entity)
            ),
            'content' => $this->_application->Summarize($comment->body_html, 200),
            'primary_link' => $this->_application->Entity_Permalink($entity),
            'type' => 'new_comment',
            'item_id' => $entity->getId(),
            'secondary_item_id' => $comment->id,
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-discuss',
        ));
    }
    
    public function onQuestionsUserProfileActivityFilter(&$activity, $identity, $addon)
    {
        if (!function_exists('bp_core_get_user_domain')) return;
        
        $user_domain = trailingslashit(bp_core_get_user_domain($identity->id)) . $this->_config['nav_slug'];
        foreach (array_keys($activity['stats']) as $bundle_name) {
            switch (@$activity['stats'][$bundle_name]['type']) {
                case 'questions':
                    $url = $user_domain . '/questions?category=' . $this->_application->getAddon($addon)->getCategoriesBundleName();
                    break;
                case 'questions_answers':
                    $url = $user_domain . '/answers?addon=' . $addon;
                    break;
                default:
                    continue 2;
            }
            $activity['stats'][$bundle_name]['url'] = $url;
        }
    }
    
    public function systemGetAdminSettingsForm()
    {
        return array(
            'nav_name' => array(
                '#type' => 'textfield',
                '#title' => __('BuddyPress profile tab label', 'sabai-discuss'),
                '#default_value' => $this->_config['nav_name'],
                '#required' => true,
            ),
            'nav_slug' => array(
                '#type' => 'textfield',
                '#title' => __('BuddyPress profile tab slug', 'sabai-discuss'),
                '#default_value' => $this->_config['nav_slug'],
                '#required' => true,
                '#alnum' => true,
            ),
            'nav_position' => array(
                '#type' => 'number',
                '#title' => __('BuddyPress profile tab position', 'sabai-discuss'),
                '#default_value' => $this->_config['nav_position'],
                '#required' => true,
                '#size' => 4,
                '#integer' => true,
            ),
            'activities' => array(
                '#type' => 'checkboxes',
                '#title' => __('BuddyPress activities', 'sabai-discuss'),
                '#default_value' => $this->_config['activities'],
                '#options' => array(
                    'questions' => __('Questions', 'sabai-discuss'),
                    'answers' => __('Answers', 'sabai-discuss'),
                    'comments' => __('Comments', 'sabai-discuss'),
                ),
                '#class' => 'sabai-form-inline',
            ),
        );
    }
}
