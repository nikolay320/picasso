<?php
class Sabai_Addon_DirectoryBuddyPress extends Sabai_Addon
    implements Sabai_Addon_System_IAdminSettings
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-directory';
    
    public function isUninstallable($currentVersion)
    {
        return true;
    }
    
    public function getDefaultConfig()
    {
        return array(
            'nav_name' => $this->_application->getAddon('Directory')->getTitle('directory'),
            'nav_slug' => 'directory',
            'nav_position' => 21,
            'activities' => array('listings', 'reviews', 'photos', 'comments'),
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
            && !in_array($activityType, array('new_directory_listing', 'new_directory_listing_review', 'new_directory_listing_photo', 'new_comment'));
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
            'default_subnav_slug' => 'listings',
        ));
        // Add sub menu items
        $navs = array(
            'listings' => __('Listings', 'sabai-directory'),
            'reviews' => __('Reviews', 'sabai-directory'),
            'photos' => __('Photos', 'sabai-directory'),
        );
        if ($this->_application->isAddonLoaded('DirectoryBookmarks')) {
            $navs['bookmarks'] = __('Bookmarks', 'sabai-directory');
        }
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
    
    public function bpNavListingsScreenFunction()
    {
        $this->_bpNavScreenFunction('Listings');
    }
    
    public function bpNavListingsContent()
    {
        echo do_shortcode(sprintf(
            '[sabai-directory return=1 hide_searchbox=1 claimed_only=%d user_id=%d]',
            $this->_application->getAddon('Directory')->getConfig('display', 'prof_claimed_only'),
            bp_displayed_user_id()
        ));
    }
    
    public function bpNavReviewsScreenFunction()
    {
        $this->_bpNavScreenFunction('Reviews');
    }
    
    public function bpNavReviewsContent()
    {
        echo do_shortcode('[sabai-directory-reviews return=1 user_id=' . bp_displayed_user_id() . ']');
    }
    
    public function bpNavPhotosScreenFunction()
    {
        $this->_bpNavScreenFunction('Photos');
    }
    
    public function bpNavPhotosContent()
    {
        echo do_shortcode('[sabai-directory-photos return=1 user_id=' . bp_displayed_user_id() . ']');
    }
 
    public function bpNavBookmarksScreenFunction()
    {
        $this->_bpNavScreenFunction('Bookmarks');
    }
    
    public function bpNavBookmarksContent()
    {
        echo do_shortcode('[sabai-directory-bookmarks return=1 user_id=' . bp_displayed_user_id() . ']');
    }
    
    public function onEntityCreateContentDirectoryListingEntitySuccess($bundle, $entity, $values)
    {   
        if (!function_exists('bp_activity_add')
            || !in_array('listings', $this->_config['activities'])
        ) return;
        
        $addon = $this->_application->Entity_Addon($entity);
        $user_id = $entity->getAuthorId();
        bp_activity_add(array(
            'user_id' => $user_id,
            'action' => sprintf(
                __('%s added a new listing to %s', 'sabai-directory'),
                $user_id ? bp_core_get_userlink($user_id) : $this->_application->Entity_Author($entity)->name,
                '<a href="' . $this->_application->Url('/' . $addon->getSlug('directory')) . '">' . $addon->getTitle('directory') . '</a>'
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
            'component' => 'sabai-directory',
        ));
    }
    
    public function onEntityCreateContentDirectoryListingReviewEntitySuccess($bundle, $entity, $values)
    {
        if (!function_exists('bp_activity_add')
            || !in_array('reviews', $this->_config['activities'])
        ) return;
        
        if (!$listing = $this->_application->Content_ParentPost($entity, false)) return;
        
        $user_id = $entity->getAuthorId();
        bp_activity_add(array(
            'user_id' => $user_id,
            'action' => sprintf(
                __('%s wrote a review for %s', 'sabai-directory'),
                $user_id ? bp_core_get_userlink($user_id) : $this->_application->Entity_Author($entity)->name,
                $this->_application->Entity_Permalink($listing)
            ),
            'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                $permalink = $this->_application->Entity_Permalink($entity), 
                $this->_application->Entity_RenderField($entity, 'directory_rating'),
                $this->_application->Summarize($entity->getContent(), 200)
            )),
            'primary_link' => $permalink,
            'type' => 'new_' . $bundle->type,
            'item_id' => $listing->getId(),
            'secondary_item_id' => $entity->getId(),
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-directory',
        ));
    }
    
    public function onEntityCreateContentDirectoryListingPhotoEntitySuccess($bundle, $entity, $values)
    {
        if (!function_exists('bp_activity_add')
            || !in_array('photos', $this->_config['activities'])
        ) return;
        
        if ($entity->content_reference // photo submitted with a review
            || $entity->getSingleFieldValue('directory_photo', 'official') // photo submitted with a listing
        ) {
            return;
        }
        
        if (!$listing = $this->_application->Content_ParentPost($entity, false)) return;
        
        bp_activity_add(array(
            'user_id' => $entity->getAuthorId(),
            'action' => sprintf(
                __('%s added a new photo to %s', 'sabai-directory'),
                bp_core_get_userlink($entity->getAuthorId()),
                $this->_application->Entity_Permalink($listing)
            ),
            'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                Sabai::h($entity->getTitle()),
                '<img src="' . $this->_application->Directory_PhotoUrl($entity, 'large') . '" alt="' . Sabai::h($entity->getTitle()) . '" />',
            )),
            'primary_link' => $this->_application->Entity_Permalink($entity),
            'type' => 'new_' . $bundle->type,
            'item_id' => $listing->getId(),
            'secondary_item_id' => $entity->getId(),
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-directory',
        ));
    }
    
    public function onCommentSubmitCommentSuccess($comment, $isEdit, $entity)
    {
        if ($isEdit
            || !function_exists('bp_activity_add')
            || !in_array('comments', $this->_config['activities'])
        ) return;
        
        switch ($entity->getBundleType()) {
            case 'directory_listing_review':
                $action = __('%1$s commented on a <a href="%2$s">review</a>', 'sabai-directory');
                break;
            case 'directory_listing_photo':
                $action = __('%1$s commented on a <a href="%2$s">photo</a>', 'sabai-directory');
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
            'component' => 'sabai-directory',
        ));
    }
    
    public function onDirectoryUserProfileActivityFilter(&$activity, $identity)
    {
        if (!function_exists('bp_core_get_user_domain')) return;
        
        $user_domain = trailingslashit(bp_core_get_user_domain($identity->id)) . $this->_config['nav_slug'];
        foreach (array_keys($activity) as $addon) {
            foreach (array_keys($activity[$addon]['stats']) as $bundle_name) {
                switch (@$activity[$addon]['stats'][$bundle_name]['type']) {
                    case 'directory_listing':
                        $url = $user_domain . '/listings?category=' . $this->_application->getAddon($addon)->getCategoryBundleName();
                        break;
                    case 'directory_listing_review':
                        $url = $user_domain . '/reviews?addon=' . $addon;
                        break;
                    case 'directory_listing_photo':
                        $url = $user_domain . '/photos?addon=' . $addon;
                        break;
                    default:
                        continue 2;
                }
                $activity[$addon]['stats'][$bundle_name]['url'] = $url;
            }
        }
    }
    
    public function systemGetAdminSettingsForm()
    {
        return array(
            'nav_name' => array(
                '#type' => 'textfield',
                '#title' => __('BuddyPress profile tab label', 'sabai-directory'),
                '#default_value' => $this->_config['nav_name'],
                '#required' => true,
            ),
            'nav_slug' => array(
                '#type' => 'textfield',
                '#title' => __('BuddyPress profile tab slug', 'sabai-directory'),
                '#default_value' => $this->_config['nav_slug'],
                '#required' => true,
                '#alnum' => true,
            ),
            'nav_position' => array(
                '#type' => 'number',
                '#title' => __('BuddyPress profile tab position', 'sabai-directory'),
                '#default_value' => $this->_config['nav_position'],
                '#required' => true,
                '#size' => 4,
                '#integer' => true,
            ),
            'activities' => array(
                '#type' => 'checkboxes',
                '#title' => __('BuddyPress activities', 'sabai-directory'),
                '#default_value' => $this->_config['activities'],
                '#options' => array(
                    'listings' => __('Listings', 'sabai-directory'),
                    'reviews' => __('Reviews', 'sabai-directory'),
                    'photos' => __('Photos', 'sabai-directory'),
                    'comments' => __('Comments', 'sabai-directory'),
                ),
                '#class' => 'sabai-form-inline',
            ),
        );
    }
}
