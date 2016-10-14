<?php
class Sabai_Addon_Directory_Controller_Admin_Settings extends Sabai_Addon_System_Controller_Admin_Settings
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig('display');
        if (!isset($config['listing_tabs'])) {
            $listing_tabs_options = $listing_default_tabs = $this->getAddon()->getListingDefaultTabs();
            unset($listing_default_tabs['sample']); // unselect sample tab
            $config['listing_tabs'] = array(
                'options' => $listing_tabs_options,
                'default' => array_keys($listing_default_tabs),
            );
        } else {
            $config['listing_tabs']['options'] += $this->getAddon()->getListingDefaultTabs();
        }
        $sorts = array(
            'newest' => __('Newest First', 'sabai-directory'),
            'oldest' => __('Oldest First', 'sabai-directory'),
            'reviews' => __('Most Reviews', 'sabai-directory'),
            'rating' => __('Highest Rated', 'sabai-directory'),
            'title' => _x('Title', 'sort', 'sabai-directory'),
            'random' => __('Random', 'sabai-directory'),
            'claimed'  => __('Claimed', 'sabai-directory'),
            'unclaimed'  => __('Unclaimed', 'sabai-directory'),
            'distance' => __('Distance', 'sabai-directory'),
        ) + $this->_getSortableFields($this->getAddon()->getListingBundleName());
        $review_sorts = array(
            'newest' => __('Newest First', 'sabai-directory'),
            'oldest' => __('Oldest First', 'sabai-directory'),
            'rating' => __('Rating', 'sabai-directory'),
            'helpfulness' => __('Helpfullness', 'sabai-directory'),
            'random' => __('Random', 'sabai-directory'),
        ) + $this->_getSortableFields($this->getAddon()->getReviewBundleName());
        $photo_sorts = array(
            'newest' => __('Newest First', 'sabai-directory'),
            'oldest' => __('Oldest First', 'sabai-directory'),
            'random' => __('Random', 'sabai-directory'),
            'votes' => __('Most Voted', 'sabai-directory'),
        );
        $view_options = array(
            'list' => __('List view', 'sabai-directory'),
            'grid' => __('Grid view', 'sabai-directory'),
        );
        if ($this->isAddonLoaded('GoogleMaps')) {
            $view_options['map'] = __('Map view', 'sabai-directory');
        }
        $form = array(
            '#tree' => true,
            '#collapsed' => false,
            'perpage' => array(
                '#type' => 'number',
                '#title' => __('Listings per page', 'sabai-directory'),
                '#default_value' => $config['perpage'],
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#display_unrequired' => true,
                '#max_value' => 500,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-directory'), 500),
            ),
            'review_perpage' => array(
                '#type' => 'number',
                '#title' => __('Reviews per page', 'sabai-directory'),
                '#default_value' => $config['review_perpage'],
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#display_unrequired' => true,
                '#max_value' => 100,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-directory'), 100),
            ),
            'photo_perpage' => array(
                '#type' => 'number',
                '#title' => __('Photos per page', 'sabai-directory'),
                '#default_value' => isset($config['photo_perpage']) ? $config['photo_perpage'] : 20,
                '#size' => 5,
                '#integer' => true,
                '#required' => true,
                '#display_unrequired' => true,
                '#max_value' => 100,
                '#min_value' => 1,
                '#field_suffix' => sprintf(__('(max. limit %d)', 'sabai-directory'), 100),
            ),
            'sorts' => array(
                '#type' => 'checkboxes',
                '#default_value' => isset($config['sorts']) ? $config['sorts'] : array_keys($sorts),
                '#title' => __('Listings sorting options', 'sabai-directory'),
                '#options' => $sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['sort'],
                '#title' => __('Listings default sorting order', 'sabai-directory'),
                '#options' => $sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'review_sorts' => array(
                '#type' => 'checkboxes',
                '#default_value' => isset($config['review_sorts']) ? $config['review_sorts'] : array_keys($review_sorts),
                '#title' => __('Reviews sorting options', 'sabai-directory'),
                '#options' => $review_sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'review_sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['review_sort'],
                '#title' => __('Reviews default sorting order', 'sabai-directory'),
                '#options' => $review_sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'photo_sorts' => array(
                '#type' => 'checkboxes',
                '#default_value' => isset($config['photo_sorts']) ? $config['photo_sorts'] : array_keys($photo_sorts),
                '#title' => __('Photos sorting options', 'sabai-directory'),
                '#options' => $photo_sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'photo_sort' => array(
                '#type' => 'radios',
                '#default_value' => $config['photo_sort'],
                '#title' => __('Photos default sorting order', 'sabai-directory'),
                '#options' => $photo_sorts,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'views' => array(
                '#type' => 'checkboxes',
                '#default_value' => isset($config['views']) ? $config['views'] : array('list', 'grid', 'map'),
                '#title' => __('Listings view options', 'sabai-directory'),
                '#options' => $view_options,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'view' => array(
                '#type' => 'radios',
                '#default_value' => !$this->isAddonLoaded('GoogleMaps') && $config['view'] === 'map' ? 'list' : $config['view'],
                '#title' => __('Listings default view', 'sabai-directory'),
                '#options' => $view_options,
                '#class' => 'sabai-form-inline',
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'grid_columns' => array(
                '#type' => 'radios',
                '#class' => 'sabai-form-inline',
                '#title' => __('Grid view column count', 'sabai-directory'),
                '#options' => array(2 => 2, 3 => 3, 4 => 4, 6 => 6),
                '#default_value' => isset($config['grid_columns']) ? $config['grid_columns'] : 4,
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'no_masonry' => array(
                '#type' => 'yesno',
                '#title' => __('Disable masonry layout in Grid view', 'sabai-directory'),
                '#default_value' => !empty($config['no_masonry']),
            ),
            'listing_tabs' => array(
                '#type' => 'options',
                '#title' => __('Single listing page tabs', 'sabai-directory'),
                '#multiple' => true,
                '#default_value' => $config['listing_tabs'],
                '#options_value_disabled' => array_keys($this->getAddon()->getListingDefaultTabs()),
                '#value_title' => __('slug', 'sabai-directory'),
                '#value_regex' => '/^[a-z0-9][a-z0-9_]*[a-z0-9]$/',
                '#value_regex_error_message' => __('Slugs must consist of lowercase alphanumeric characters and underscores.', 'sabai-directory'),
            ),
            'category_columns' => array(
                '#type' => 'radios',
                '#class' => 'sabai-form-inline',
                '#title' => __('Category list page column count', 'sabai-directory'),
                '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6),
                '#default_value' => isset($config['category_columns']) ? $config['category_columns'] : 2,
                '#required' => true,
                '#display_unrequired' => true,
            ),
            'category_hide_empty' => array(
                '#type' => 'yesno',
                '#title' => __('Hide empty categories on category list page', 'sabai-directory'),
                '#default_value' => !empty($config['category_hide_empty']),
            ),
            'category_hide_count' => array(
                '#type' => 'yesno',
                '#title' => __('Hide post counts on category list page', 'sabai-directory'),
                '#default_value' => !empty($config['category_hide_count']),
            ),
            'category_hide_children' => array(
                '#type' => 'yesno',
                '#title' => __('Hide child categories on category list page', 'sabai-directory'),
                '#default_value' => !empty($config['category_hide_children']),
            ),
            'category_child_count' => array(
                '#type' => 'number',
                '#title' => __('Number of child categories to display on category list page', 'sabai-directory'),
                '#default_value' => @$config['category_child_count'],
                '#min_value' => 0,
                '#max_value' => 10,
                '#states' => array(
                    'visible' => array(
                        'input[name="category_hide_children"]' => array('type' => 'value', 'value' => 0),
                    ),
                ),
                '#size' => 3,
            ),
            'no_photo_comments' => array(
                '#type' => 'yesno',
                '#title' => __('Disable photo comments', 'sabai-directory'),
                '#default_value' => !empty($config['no_photo_comments']),
            ),
            'stick_featured' => array(
                '#type' => 'yesno',
                '#title' => __('Stick featured listings to the top of the directory index page', 'sabai-directory'),
                '#default_value' => !empty($config['stick_featured']),
            ),
            'stick_featured_cat' => array(
                '#type' => 'yesno',
                '#title' => __('Stick featured listings to the top of single category pages', 'sabai-directory'),
                '#default_value' => isset($config['stick_featured_cat']) ? !empty($config['stick_featured_cat']) : !empty($config['stick_featured']), // compat with <1.3
            ),
        );
        if (!$this->getAddon()->hasParent()) {
            $form += array(
                'prof_claimed_only' => array(
                    '#type' => 'yesno',
                    '#title' => __('List claimed listings only in user profile', 'sabai-directory'),
                    '#default_value' => !empty($config['prof_claimed_only']),
                ),
                'register' => array(
                    '#type' => 'yesno',
                    '#title' => __('Show user registration form when guest user is submitting a listing', 'sabai-directory'),
                    '#default_value' => !empty($config['register']),
                ),
                'register_skip' => array(
                    '#type' => 'yesno',
                    '#title' => __('Allow guest user to skip user registration', 'sabai-directory'),
                    '#default_value' => !empty($config['register_skip']),
                    '#states' => array(
                        'visible' => array(
                            'input[name="register"]' => array('value' => 1),
                        ),
                    ),
                ),
            );
        }
        if ($this->getAddon()->getConfig('map', 'disable')) {
            $form['view']['#options_disabled'] = $form['views']['#options_disabled'] = array('map');
        }
        
        return $form;
    }
    
    protected function _getSuccessUrl(Sabai_Context $context)
    {
        return $this->Url($context->getRoute());
    }
    
    protected function _saveConfig(Sabai_Context $context, array $values)
    {
        $this->getAddon()->saveConfig(array('display' => $values + $this->getAddon()->getConfig('display')));
        // Run upgrade process to refresh all slug data
        $this->UpgradeAddons(array($this->getAddon()->getName()));
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
