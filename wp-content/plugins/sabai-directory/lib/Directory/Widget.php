<?php
class Sabai_Addon_Directory_Widget implements Sabai_Addon_Widgets_IWidget
{
    private $_addon, $_name;
    
    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function widgetsWidgetGetTitle()
    {
        switch ($this->_name) {
            case 'recent':
                return __('Recent Listings', 'sabai-directory');
            case 'recent_reviews':
                return __('Recent Listing Reviews', 'sabai-directory');
            case 'recent_photos':
                return __('Recent Listing Photos', 'sabai-directory');
            case 'featured':
                return __('Featured Listings', 'sabai-directory');
            case 'related':
                return __('Related Listings', 'sabai-directory');
            case 'nearby':
                return __('Nearby Listings', 'sabai-directory');
            case 'categories':
                return __('Listing Categories', 'sabai-directory');
            case 'submitbtn':
                return __('Add Listing', 'sabai-directory');
            case 'listing_map':
                return __('Listing Map', 'sabai-directory');
            case 'listing_photos':
                return __('Listing Photos', 'sabai-directory');
            case 'contact':
                return __('Contact Listing', 'sabai-directory');
        }
    }
    
    public function widgetsWidgetGetSummary()
    {
        switch ($this->_name) {
            case 'recent':
                return __('Recently posted listings', 'sabai-directory');
            case 'recent_reviews':
                return __('Recently posted listing reviews', 'sabai-directory');
            case 'recent_photos':
                return __('Recently posted listing photos', 'sabai-directory');
            case 'featured':
                return __('Featured Listings', 'sabai-directory');
            case 'recent':
                return __('Related Listings', 'sabai-directory');
            case 'nearby':
                return __('Nearby Listings', 'sabai-directory');
            case 'categories':
                return __('A list of categories', 'sabai-directory');
            case 'submitbtn':
                return __('A call to action button', 'sabai-directory');      
            case 'listing_map':
                return __('Location map of current listing', 'sabai-directory');
            case 'listing_photos':
                return __('Photos of current listing', 'sabai-directory');
            case 'contact':
                return __('Contact us form for currently displayed listing', 'sabai-directory');
        }
    }
    
    public function widgetsWidgetGetSettings()
    {
        $settings = array(
            'no_cache' => array(
                '#type' => 'checkbox',
                '#title' => __('Do not cache output', 'sabai-directory'),
                '#default_value' => false,
                '#weight' => 99,
            ),
        );
        switch ($this->_name) {
            case 'recent':
                $settings += array(
                    'bundle' => $this->_getSelectDirectoryCategoryField(),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of listings to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                        '#size' => 3,
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                        '#size' => 6,
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Newest First', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => 'post_published', 
                    ),
                    'claimed_only' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show claimed listings only', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                );
                $settings += $this->_getListingMetaOptions(array('show_time' => true));
                break;
            case 'recent_reviews':
                $settings += array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = array('' => __('All directories', 'sabai-directory')) + $this->_addon->getApplication()->Directory_DirectoryList('review'),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => '',
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of reviews to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                        '#size' => 3,
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                        '#size' => 6,
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Newest First', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => 'post_published', 
                    ),
                    'show_time' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show published date', 'sabai-directory'),
                        '#default_value' => true,
                    ),
                    'show_author' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show author', 'sabai-directory'),
                        '#default_value' => true,
                    ),
                );    
                break;
            case 'recent_photos':
                $settings += array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options = array('' => __('All directories', 'sabai-directory')) + $this->_addon->getApplication()->Directory_DirectoryList('photo'),
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => '',
                    ),
                    'cols' => array(
                        '#type' => 'select',
                        '#title' => __('Number of columns', 'sabai-directory'),
                        '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6),
                        '#default_value' => 3, 
                    ),
                    'rows' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of rows', 'sabai-directory'),
                        '#integer' => true,
                        '#min_value' => 1,
                        '#max_value' => 10,
                        '#default_value' => 4, 
                        '#size' => 3,
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Newest First', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => 'post_published', 
                    ),
                    'photo_type' => array(
                        '#type' => 'select',
                        '#title' => __('Photo type', 'sabai-directory'),
                        '#options' => array(
                            'official' => __('Official photos only', 'sabai-directory'),
                            'non-official' => __('Non-official photos only', 'sabai-directory'),
                            '' => __('All photos', 'sabai-directory'),
                        ),
                        '#default_value' => '', 
                    ),
                );
                break;
            case 'featured':
                $settings += array(
                    'bundle' => $this->_getSelectDirectoryCategoryField(),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of listings to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                        '#size' => 3,
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Newest First', 'sabai-directory'),
                            'voting_rating.average' => __('Highest Rated', 'sabai-directory'),
                            'content_children_count.value' => __('Most Reviews', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => '_random', 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                        '#size' => 6,
                    ),
                    'claimed_only' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show claimed listings only', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                );
                $settings += $this->_getListingMetaOptions(array('show_num_reviews' => true, 'show_num_photos' => true));
                break;
            case 'related':
                $settings += array(
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of listings to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                        '#size' => 3,
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Newest First', 'sabai-directory'),
                            'voting_rating.average' => __('Highest Rated', 'sabai-directory'),
                            'content_children_count.value' => __('Most Reviews', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => '_random', 
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                        '#size' => 6,
                    ),
                    'claimed_only' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show claimed listings only', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                );
                $settings += $this->_getListingMetaOptions(array('show_rating' => true));
                break;
            case 'nearby':
                $settings += array(
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of listings to show', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 5, 
                        '#size' => 3,
                    ),
                    'summary' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show summary', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'num_chars' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of characters in the summary', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 100, 
                        '#size' => 6,
                    ),
                    'claimed_only' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show claimed listings only', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                );
                $settings += $this->_getListingMetaOptions(array('show_rating' => true));
                break;
            case 'categories':
                $directory_options = $this->_addon->getApplication()->Directory_DirectoryList('category');
                $directory_options_keys = array_keys($directory_options);
                return $settings + array(
                    'bundle' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options,
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift($directory_options_keys),
                    ),
                    'depth' => array(
                        '#type' => 'textfield',
                        '#title' => __('Category depth (0 for unlimited)', 'sabai-directory'),
                        '#integer' => true,
                        '#default_value' => 0, 
                        '#size' => 3,
                    ),
                    'post_count' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show post count', 'sabai-directory'),
                        '#default_value' => true, 
                    ),
                    'thumbnail' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Show thumbnail', 'sabai-directory'),
                        '#default_value' => false, 
                    ),
                    'no_posts_hide' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Hide if no posts', 'sabai-directory'),
                        '#default_value' => false, 
                    ),
                );
            case 'submitbtn':
                $directory_options = array('' => __('All directories', 'sabai-directory')) + $this->_addon->getApplication()->Directory_DirectoryList('addon');
                $directory_options_keys = array_keys($directory_options);
                return array(   
                    'addon' => array(
                        '#title' => __('Select directory', 'sabai-directory'),
                        '#options' => $directory_options,
                        '#type' => count($directory_options) <= 1 ? 'hidden' : 'select',
                        '#default_value' => array_shift($directory_options_keys),
                    ),
                    'label' => array(
                        '#title' => __('Button label', 'sabai-directory'),
                        '#type' => 'textfield',
                        '#default_value' => __('Add Listing', 'sabai-directory'), 
                    ),
                    'size' => array(
                        '#type' => 'select',
                        '#title' => __('Button size', 'sabai-directory'),
                        '#options' => array(
                            'xs' => __('Mini', 'sabai-directory'),
                            'sm' => __('Small', 'sabai-directory'),
                            '' => __('Medium', 'sabai-directory'),
                            'lg' => __('Large', 'sabai-directory'),
                        ),
                        '#default_value' => 'lg', 
                    ),
                    'color' => array(
                        '#type' => 'select',
                        '#title' => __('Button color', 'sabai-directory'),
                        '#options' => array(
                            'default' => __('White', 'sabai-directory'),
                            'primary' => __('Blue', 'sabai-directory'),
                            'info' => __('Light blue', 'sabai-directory'),
                            'success' => __('Green', 'sabai-directory'),
                            'warning' => __('Orange', 'sabai-directory'),
                            'danger' => __('Red', 'sabai-directory'),
                        ),
                        '#default_value' => 'success', 
                    ),
                );
            case 'listing_map':
                if (!$this->_addon->getApplication()->isAddonLoaded('GoogleMaps')) {
                    return array(
                        'header' => array(
                            '#type' => 'markup',
                            '#markup' => '<div class="sabai-alert-warning">GoogleMaps add-on is required for this widget to work.</div>',
                        ),
                    );
                }
                return $this->_addon->getApplication()->GoogleMaps_MapOptions(array(), true);
            case 'contact':
                return array(
                    'btn_label' => array(
                        '#title' => __('Button label', 'sabai-directory'),
                        '#type' => 'textfield',
                        '#default_value' => __('Submit', 'sabai-directory'), 
                    ),
                    'btn_pos' => array(
                        '#type' => 'select',
                        '#title' => __('Button position', 'sabai-directory'),
                        '#options' => array(
                            'left' => __('Align left', 'sabai-directory'),
                            'right' => __('Align right', 'sabai-directory'),
                        ),
                        '#default_value' => 'left', 
                    ),
                );
            case 'listing_photos':
                return array (
                    'size' => array(
                        '#title' => __('Photo size', 'sabai-directory'),
                        '#type' => 'select',
                        '#options' => array(
                            'thumbnail' => __('Thumbnail photo', 'sabai-directory'),
                            'medium' => __('Medium size photo', 'sabai-directory'),
                            'large' => __('Large size photo', 'sabai-directory'),
                            '' => __('Original size photo', 'sabai-directory'),
                        ),
                        '#default_value' => 'medium',
                        '#weight' => 5,
                    ),
                    'num' => array(
                        '#type' => 'textfield',
                        '#title' => __('Number of photos', 'sabai-directory'),
                        '#integer' => true,
                        '#min_value' => 1,
                        '#default_value' => 5,
                        '#size' => 3,
                    ),
                    'sort' => array(
                        '#type' => 'select',
                        '#title' => __('Sort by', 'sabai-directory'),
                        '#options' => array(
                            'post_published' => __('Newest First', 'sabai-directory'),
                            '_random' => __('Random', 'sabai-directory'),
                        ),
                        '#default_value' => 'post_published', 
                    ),
                    'photo_type' => array(
                        '#type' => 'select',
                        '#title' => __('Photo type', 'sabai-directory'),
                        '#options' => array(
                            'official' => __('Official photos only', 'sabai-directory'),
                            'non-official' => __('Non-official photos only', 'sabai-directory'),
                            '' => __('All photos', 'sabai-directory'),
                        ),
                        '#default_value' => '', 
                    ),
                ) + $this->_addon->getApplication()->CarouselOptions(array(), true);
        }
        return $this->_addon->getApplication()->Filter('directory_widget_settings', $settings, array($this->_name));
    }
    
    public function widgetsWidgetGetLabel()
    {
        switch ($this->_name) {
            case 'submitbtn':
                return '';
            default:
                return $this->widgetsWidgetGetTitle();
        }
    }
    
    public function widgetsWidgetGetContent(array $settings)
    {
        if ($this->_name === 'submitbtn') {
            return $this->_getSubmitButton($settings);
        } elseif ($this->_name === 'related') {
            return $this->_getRelatedListings($settings);
        } elseif ($this->_name === 'nearby') {
            return $this->_getNearbyListings($settings);
        } elseif ($this->_name === 'listing_map') {
            return $this->_getListingMap($settings);
        } elseif ($this->_name === 'listing_photos') {
            return $this->_getListingPhotos($settings);
        } elseif ($this->_name === 'contact') {
            return $this->_getContactForm($settings);
        }
        
        if (!empty($settings['no_cache'])
            || false === $ret = $this->_addon->getApplication()
                ->getPlatform()
                ->getCache($cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . md5(serialize($settings)))
        ) {
            switch ($this->_name) {
                case 'categories':
                    $ret = $this->_getCategories($settings);
                    break;
                case 'recent_photos':
                    $ret = $this->_getRecentPhotos($settings);
                    break;
                case 'recent_reviews':            
                    $ret = $this->_getRecentReviews($settings);
                    break;
                case 'recent':            
                    $ret = $this->_getRecentListings($settings);
                    break;
                case 'featured': 
                    $ret = $this->_getFeaturedListings($settings);
                    break;
                default:
                    return;
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
    
    private function _getRecentListings($settings)
    {
        $application = $this->_addon->getApplication();
        $query = $application->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if (is_numeric($settings['bundle'])) {
            $category_ids = array($settings['bundle']);
            foreach ($application->Taxonomy_Descendants($settings['bundle'], false) as $_category) {
                $category_ids[] = $_category->id;
            }
            $query->fieldIsIn('directory_category', $category_ids);
        } else {
            if ($settings['bundle']) {
                $bundle_key = 'post_entity_bundle_name';
                $bundle_value = $settings['bundle'];
            } else {
                $bundle_key =  'post_entity_bundle_type';
                $bundle_value = 'directory_listing';
            }
            $query->propertyIs($bundle_key, $bundle_value);
        }
        if (!empty($settings['claimed_only'])) {
            $query->fieldIsNotNull('directory_claim', 'claimed_by');
        }
        if (!isset($settings['sort'])) {
            $settings['sort'] = 'post_published';
        }
        if ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        $query = $application->Filter('directory_widget_query', $query, array($this->_name, $settings));
        $listings = $query->fetch($settings['num']);
        if (empty($listings)) {
            return;
        }
        $photos = $this->_getListingsPhotos(array_keys($listings));
        $ret = array();
        foreach ($listings as $listing) {
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $application->Summarize($listing->getContent(), $settings['num_chars']) : null,
                'url' => $application->Entity_Url($listing),
                'title' => $listing->getTitle(),
                'meta' => $this->_getListingMeta($listing, $settings),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->NoImageUrl(true) . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true)),
                'listing' => $listing,
            );
        }
        return array('content' => $application->Filter('directory_widget_content', $ret, array($this->_name, $settings)));
    }
    
    private function _getFeaturedListings($settings)
    {
        $application = $this->_addon->getApplication();
        $query = $application->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIsNotNull('content_featured');
        if (is_numeric($settings['bundle'])) {
            $category_ids = array($settings['bundle']);
            foreach ($application->Taxonomy_Descendants($settings['bundle'], false) as $_category) {
                $category_ids[] = $_category->id;
            }
            $query->fieldIsIn('directory_category', $category_ids);
        } else {
            if ($settings['bundle']) {
                $bundle_key = 'post_entity_bundle_name';
                $bundle_value = $settings['bundle'];
            } else {
                $bundle_key =  'post_entity_bundle_type';
                $bundle_value = 'directory_listing';
            }
            $query->propertyIs($bundle_key, $bundle_value);
        }
        if (!empty($settings['claimed_only'])) {
            $query->fieldIsNotNull('directory_claim', 'claimed_by');
        }
        $settings += array('num' => 5, 'sort' => 'post_published');
        if (strpos($settings['sort'], '.')) {
            list($field, $column) = explode('.', $settings['sort']);
            $query->sortByField($field, 'DESC', $column);
        } elseif ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        $query = $application->Filter('directory_widget_query', $query, array($this->_name, $settings));
        $listings = $query->fetch($settings['num']);
        if (empty($listings)) {
            return;
        }
        $photos = $this->_getListingsPhotos(array_keys($listings));
        $ret = array();
        foreach ($listings as $listing) {
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $application->Summarize($listing->getContent(), $settings['num_chars']) : null,
                'url' => $application->Entity_Url($listing),
                'title' => $listing->getTitle(),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->NoImageUrl(true) . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true, 'title' => $listing->getTitle())),
                'meta' => $this->_getListingMeta($listing, $settings),
                'listing' => $listing,
            );
        }
        return array('content' => $application->Filter('directory_widget_content', $ret, array($this->_name, $settings)));
    }
    
    private function _getRelatedListings($settings)
    {
        if (!isset($GLOBALS['sabai_entity'])
            || !$GLOBALS['sabai_entity'] instanceof Sabai_Addon_Content_Entity
            || $GLOBALS['sabai_entity']->getBundleType() !== 'directory_listing'
        ) {
            return;
        }
        
        if (!empty($settings['no_cache'])
            || false === $ret = $this->_addon->getApplication()
                ->getPlatform()
                ->getCache($cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . $GLOBALS['sabai_entity']->getId())
        ) {
            $ret = $this->_doGetRelatedListings($GLOBALS['sabai_entity'], $settings);
            if (empty($settings['no_cache'])) {
                $this->_addon->getApplication()->getPlatform()->setCache($ret, $cache_id, 3600);
            }
        }
        return $ret ? array(
            'content' => $ret,
            'link' => array(
                'url' => $this->_addon->getApplication()->Entity_Url($GLOBALS['sabai_entity'], '/related'),
                'title' => __('More Listings'),
            ),
        ) : null;
    }
    
    private function _doGetRelatedListings($entity, $settings)
    {        
        $application = $this->_addon->getApplication();
        $query = $application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $entity->getBundleName())
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIsNot('post_id', $entity->getId());
        if (!empty($entity->directory_category)) {
            $term_ids = array();
            foreach ($entity->directory_category as $category) {
                $term_ids[] = $category->getId();
            }
            $query->fieldIsIn('directory_category', $term_ids);
        } else {
            $query->fieldIsNull('directory_category');
        }
        if (!empty($settings['claimed_only'])) {
            $query->fieldIsNotNull('directory_claim', 'claimed_by');
        }
        $settings += array('num' => 5, 'sort' => 'post_published');
        if (strpos($settings['sort'], '.')) {
            list($field, $column) = explode('.', $settings['sort']);
            $query->sortByField($field, 'DESC', $column);
        } elseif ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        $query = $application->Filter('directory_widget_query', $query, array($this->_name, $settings, $entity));
        $listings = $query->fetch($settings['num']);
        if (empty($listings)) {
            return;
        }
        $photos = $this->_getListingsPhotos(array_keys($listings));
        $ret = array();
        foreach ($listings as $listing) {
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $application->Summarize($listing->getContent(), $settings['num_chars']) : null,
                'url' => $application->Entity_Url($listing),
                'title' => $listing->getTitle(),
                'meta' => $this->_getListingMeta($listing, $settings),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->NoImageUrl(true) . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true)),
                'listing' => $listing,
            );
        }
        return $application->Filter('directory_widget_content', $ret, array($this->_name, $settings, $entity));
    }
    
    private function _getNearbyListings($settings)
    {
        if (!isset($GLOBALS['sabai_entity'])
            || !$GLOBALS['sabai_entity'] instanceof Sabai_Addon_Content_Entity
            || $GLOBALS['sabai_entity']->getBundleType() !== 'directory_listing'
        ) {
            return;
        }
        
        if (!empty($settings['no_cache'])
            || false === $ret = $this->_addon->getApplication()
                ->getPlatform()
                ->getCache($cache_id = $this->_addon->getName() . '_widget_' . $this->_name . '_' . $GLOBALS['sabai_entity']->getId())
        ) {
            $ret = $this->_doGetNearbyListings($GLOBALS['sabai_entity'], $settings);
            if (empty($settings['no_cache'])) {
                $this->_addon->getApplication()->getPlatform()->setCache($ret, $cache_id, 3600);
            }
        }
        return $ret ? array(
            'content' => $ret,
            'link' => array(
                'url' => $this->_addon->getApplication()->Entity_Url($GLOBALS['sabai_entity'], '/nearby'),
                'title' => __('More Listings'),
            ),
        ) : null;
    }
    
    private function _doGetNearbyListings($entity, $settings)
    {
        $lat = $entity->directory_location[0]['lat'];
        $lng = $entity->directory_location[0]['lng'];
        if (!$lat || !$lng) return;
        
        $application = $this->_addon->getApplication();
        $query = $application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $entity->getBundleName())
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIsNot('post_id', $entity->getId());
        if (!empty($settings['claimed_only'])) {
            $query->fieldIsNotNull('directory_claim', 'claimed_by');
        }
        $is_mile = $this->_addon->getConfig('map', 'distance_mode') === 'mil';
        $target = array(
            'table' => array(
                $application->getDB()->getResourcePrefix() . 'entity_field_directory_location'  => array(
                    'alias' => 'directory_location',
                    'on' => null,
                ),
            ),
            'column' => sprintf(
                '(%1$d * acos(cos(radians(%2$.6F)) * cos(radians(directory_location.lat)) * cos(radians(directory_location.lng) - radians(%3$.6F)) + sin(radians(%2$.6F)) * sin(radians(directory_location.lat))))',
                $is_mile ? 3959 : 6371,
                $lat,
                $lng
            ),
            'column_type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
            'is_property' => false,
            'field_name' => false,
        );
        $query->addCriteria(new SabaiFramework_Criteria_IsOrSmallerThan($target, 100));
        $settings += array('num' => 5);
        $query = $application->Filter('directory_widget_query', $query, array($this->_name, $settings, $entity));
        $query->sortByExtraField('distance', 'ASC')->addExtraField(
            'distance',
            sprintf(
                '(%1$d * acos(cos(radians(%2$.6F)) * cos(radians(directory_location.lat)) * cos(radians(directory_location.lng) - radians(%3$.6F)) + sin(radians(%2$.6F)) * sin(radians(directory_location.lat))))',
                $is_mile ? 3959 : 6371,
                $lat,
                $lng
            )
        );
        $listings = $query->fetch($settings['num']);
        if (empty($listings)) {
            return;
        }
        $photos = $this->_getListingsPhotos(array_keys($listings));
        $ret = array();
        foreach ($listings as $listing) {
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $application->Summarize($listing->getContent(), $settings['num_chars']) : null,
                'url' => $application->Entity_Url($listing),
                'title' => $listing->getTitle(),
                'meta' => $this->_getListingMeta($listing, $settings),
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->NoImageUrl(true) . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true)),
                'listing' => $listing,
            );
        }
        return $application->Filter('directory_widget_content', $ret, array($this->_name, $settings, $entity));
    }
    
    private function _getListingsPhotos($listingIds)
    {
        $photos = $this->_addon->getApplication()->Entity_Query()
            ->propertyIs('post_entity_bundle_type', 'directory_listing_photo')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIsIn('content_parent', $listingIds)
            ->fieldIsNotNull('directory_photo', 'official') // official photos
            ->sortByField('directory_photo', 'ASC', 'display_order')
            ->fetch();
        $ret = array();
        foreach ($photos as $photo) {
            if ($listing = $photo->getSingleFieldValue('content_parent')) {
                $ret[$listing->getId()][] = $photo;
            }
        }
        return $ret;
    }
    
    private function _getRecentReviews($settings)
    {
        $settings += array('num' => 5);
        $application = $this->_addon->getApplication();
        if ($settings['bundle']) {
            $bundle_key = 'post_entity_bundle_name';
            $bundle_value = $settings['bundle'];
        } else {
            $bundle_key =  'post_entity_bundle_type';
            $bundle_value = 'directory_listing_review';
        }
        $query = $application->Entity_Query('content')
            ->propertyIs($bundle_key, $bundle_value)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if (!isset($settings['sort'])) {
            $settings['sort'] = 'post_published';
        }
        if ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        $query = $application->Filter('directory_widget_query', $query, array($this->_name, $settings));
        $reviews = $query->fetch($settings['num']);
        if (empty($reviews)) {
            return;
        }
        $listings = $listing_ids = array();
        foreach (array_keys($reviews) as $review_id) {
            $listing = $application->Content_ParentPost($reviews[$review_id], false);
            if (!$listing) continue;
            
            $listing_ids[] = $listing->getId();
            $listings[$review_id] = $listing;
        }
        if (!empty($listing_ids)) {
            $photos = $this->_getListingsPhotos($listing_ids);
        }
        $ret = array();
        foreach (array_keys(array_intersect_key($reviews, $listings)) as $review_id) {
            $review = $reviews[$review_id];
            $listing = $listings[$review_id];
            $meta = array();
            if (!empty($settings['show_time'])) {
                $meta[] = '<i class="fa fa-clock-o"></i> ' . $application->getPlatform()->getHumanTimeDiff($review->getTimestamp());
            }
            if (!empty($settings['show_author'])) {
                $meta[] = '<i class="fa fa-user"></i> ' . Sabai::h($application->Entity_Author($review)->name);
            }
            $ret[] = array(
                'summary' => !empty($settings['summary']) ? $application->Summarize($review->getContent(), $settings['num_chars']) : null,
                'url' => $application->Entity_Url($review),
                'title_html' => $application->Voting_RenderRating($review->directory_rating['']) . '&nbsp;'
                    . $application->Entity_Permalink($review) . $application->Entity_Permalink($listing, array('atts' => array('style' => 'display:none;'))),
                'meta' => $meta,
                'image' => !isset($photos[$listing->getId()])
                    ? $application->Entity_Permalink($listing, array('no_escape' => true, 'title' => '<img src="' . $application->NoImageUrl(true) . '" alt="" />'))
                    : $application->File_ThumbnailLink($listing, $photos[$listing->getId()][0]->file_image[0], array('link_entity' => true, 'title' => $listing->getTitle())),
                'listing' => $listing,
                'review' => $review,
            );
        }
        return array('content' => $application->Filter('directory_widget_content', $ret, array($this->_name, $settings)));
    }
    
    private function _getRecentPhotos($settings)
    {
        $settings += array('rows' => 4, 'cols' => 3);
        $application = $this->_addon->getApplication();
        if ($settings['bundle']) {
            $bundle_key = 'post_entity_bundle_name';
            $bundle_value = $settings['bundle'];
        } else {
            $bundle_key =  'post_entity_bundle_type';
            $bundle_value = 'directory_listing_photo';
        }
        $query = $application->Entity_Query('content')
            ->propertyIs($bundle_key, $bundle_value)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if (!isset($settings['sort'])) {
            $settings['sort'] = 'post_published';
        }
        if ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        if ($settings['photo_type'] === 'official') {
            $query->fieldIsNotNull('directory_photo', 'official');
        } elseif ($settings['photo_type'] === 'non-official') {
            $query->fieldIsNull('directory_photo', 'official');
        }
        $query = $application->Filter('directory_widget_query', $query, array($this->_name, $settings));
        $photos = $query->fetch($settings['cols'] * $settings['rows']);
        if (empty($photos)) {
            return;
        }
        $html = array();
        $i = 0;
        $span = 12 / $settings['cols'];
        while ($_photos = array_slice($photos, $i * $settings['cols'], $settings['cols'])) {
            $html[] = '<div class="sabai-row">';
            foreach ($_photos as $photo) {
                $photo_title = Sabai::h($photo->getTitle());
                $photo_url = $application->Directory_PhotoUrl($photo, 'thumbnail');
                if ($listing = $application->Content_ParentPost($photo, false)) {
                    $html[] = sprintf(
                        '<div class="sabai-col-xs-%d"><a href="%s" rel="nofollow"><img title="%s" src="%s" alt="" /></a></div>',
                        $span,
                        $application->Entity_Url($listing, '/photos', array('photo_id' => $photo->getId(), '__fragment' => 'sabai-entity-content-' . $photo->getId())),
                        $photo_title,
                        $photo_url
                    );
                } else {
                    // For some reason, listing of the photo could not be fetched. Normally, this should not happen.
                    $html[] = sprintf('<div class="sabai-col-xs-%d"><img title="%s" src="%s" alt="" /></div>', $span, $photo_title, $photo_url);
                }
            }
            $html[] = '</div>';
            ++$i;
        }
        return $application->Filter('directory_widget_content', implode(PHP_EOL, $html), array($this->_name, $settings));
    }
    
    private function _getCategories($settings)
    {        
        return $this->_addon->getApplication()->Taxonomy_HtmlList(
            $settings['bundle'],
            array(
                'content_bundle' => 'directory_listing',
                'format' => empty($settings['post_count']) ? '%s' : __('%s (%d)', 'sabai-directory'),
                'hide_empty' => !empty($settings['no_posts_hide']),
                'depth' => (int)$settings['depth'],
                'permalink' => empty($settings['thumbnail']) ? array() : array('thumbnail' => 'directory_thumbnail', 'thumbnail_size' => 24),
            )
        );
    }
    
    private function _getSubmitButton($settings)
    {
        $application = $this->_addon->getApplication();
        $params = isset($settings['addon']) && strlen($settings['addon']) && $application->isAddonLoaded($settings['addon'])
            ? array('bundle' => $application->getAddon($settings['addon'])->getListingBundleName())
            : array();
        return sprintf(
            '<a href="%s" class="sabai-btn %s %s">%s</a>',
            $application->Url('/'. $application->getAddon('Directory')->getSlug('add-listing'), $params),
            !empty($settings['size']) ? 'sabai-btn-' . $settings['size'] : '',
            !empty($settings['color']) ? 'sabai-btn-' . $settings['color'] : '',
            Sabai::h($settings['label'])
        );
    }
    
    private function _getSelectDirectoryCategoryField()
    {
        $application = $this->_addon->getApplication();
        $directories = $application->Directory_DirectoryList('addon');
        $single_directory = count($directories) === 1;
        $options = array('' => $single_directory ? __('All categories', 'sabai-directory') : __('All directories', 'sabai-directory'));
        foreach ($directories as $addon_name => $title) {
            $addon = $application->getAddon($addon_name);
            $category_bundle = $application->Entity_Bundle($addon->getCategoryBundleName());
            $tree = $single_directory ? array() : array($addon->getListingBundleName() => $title);
            $options += $application->Taxonomy_Tree($category_bundle, array('prefix' => '--', 'init_depth' => 2), $tree);
        }
        
        return array(
            '#title' => $single_directory ? __('Select category', 'sabai-directory') : __('Select directory or category', 'sabai-directory'),
            '#options' => $options,
            '#type' => 'select',
            '#default_value' => '',
        );
    }
    
    protected function _getListingMetaOptions(array $defaults = null)
    {
        return array(
            'show_time' => array(
                '#type' => 'checkbox',
                '#title' => __('Show published date', 'sabai-directory'),
                '#default_value' => !empty($defaults['show_time']),
            ),
            'show_rating' => array(
                '#type' => 'checkbox',
                '#title' => __('Show rating', 'sabai-directory'),
                '#default_value' => !empty($defaults['show_rating']),
            ),
            'show_num_reviews' => array(
                '#type' => 'checkbox',
                '#title' => __('Show number of reviews', 'sabai-directory'),
                '#default_value' => !empty($defaults['show_num_reviews']),
            ),
            'show_num_photos' => array(
                '#type' => 'checkbox',
                '#title' => __('Show number of photos', 'sabai-directory'),
                '#default_value' => !empty($defaults['show_num_photos']),
            ),
        );
    }
    
    protected function _getListingMeta($listing, $settings)
    {
        $application = $this->_addon->getApplication();
        $meta = array();
        foreach (array_keys($settings) as $key) {
            if (empty($settings[$key])) continue;
            
            switch ($key) {
                case 'show_time':
                    $meta[2] = '<i class="fa fa-clock-o"></i> ' . $application->getPlatform()->getHumanTimeDiff($listing->getTimestamp());
                    break;
                case 'show_rating':
                    if (!empty($listing->voting_rating['']['count'])) {
                        $meta[1] = sprintf('%s<span class="sabai-voting-rating-average">%s</span><span class="sabai-voting-rating-count">(%d)</span>', $application->Voting_RenderRating($listing), number_format($listing->voting_rating['']['average'], 2), $listing->voting_rating['']['count']);
                    }
                    break;
                case 'show_num_reviews':
                    if ($count = (int)$listing->getSingleFieldValue('content_children_count', 'directory_listing_review')) {
                        $meta[3] = '<i class="fa fa-comments"></i> ' . $count;
                    }
                    break;
                case 'show_num_photos':
                    if ($count = (int)$listing->getSingleFieldValue('content_children_count', 'directory_listing_photo')) {
                        $meta[4] = '<i class="fa fa-camera"></i> ' . $count;
                    }
                    break;
            }
        }
        ksort($meta);
        return $meta;
    }
    
    private function _getListingMap($settings)
    {
        if (!isset($GLOBALS['sabai_entity'])
            || !$this->_addon->getApplication()->isAddonLoaded('GoogleMaps')
            || $GLOBALS['sabai_entity']->getBundleType() !== 'directory_listing'
            || !$GLOBALS['sabai_entity']->directory_location
        ) return array();

        return $this->_addon->getApplication()->GoogleMaps_RenderMap($GLOBALS['sabai_entity']->directory_location, array('mini' => 1) + $settings);
    }
    
    private function _getListingPhotos($settings)
    {
        if (!isset($GLOBALS['sabai_entity'])
            || $GLOBALS['sabai_entity']->getBundleType() !== 'directory_listing'
            || !$GLOBALS['sabai_entity']->directory_photos
        ) return;
        
        $app = $this->_addon->getApplication();
        $query = $app->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $app->Entity_Addon($GLOBALS['sabai_entity'])->getPhotoBundleName())
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIs('content_parent', $GLOBALS['sabai_entity']->getId());
        if ($settings['photo_type'] === 'official') {
            $query->fieldIsNotNull('directory_photo', 'official');
        } elseif ($settings['photo_type'] === 'non-official') {
            $query->fieldIsNull('directory_photo', 'official');
        }
        if (!isset($settings['sort'])) {
            $settings['sort'] = 'post_published';
        }
        if ($settings['sort'] === '_random') {
            $query->sortByRandom();
        } else {
            $query->sortByProperty($settings['sort'], 'DESC');
        }
        $query = $app->Filter('directory_widget_query', $query, array($this->_name, $settings));
        $photos = $query->fetch($settings['num']);
        
        $slides = array();
        foreach ($photos as $photo) {
            $slides[] = sprintf(
                '<img title="%s" src="%s" alt="" />',
                Sabai::h($photo->getTitle()),
                $this->_addon->getApplication()->Directory_PhotoUrl($photo, $settings['size'])
            );
        }
        return $this->_addon->getApplication()->Carousel($slides, $settings + array('auto' => false, 'captions' => false, 'pager' => false, 'controls' => false));
    }
    
    private function _getContactForm($settings)
    {
        if (!isset($GLOBALS['sabai_entity'])
            || $GLOBALS['sabai_entity']->getBundleType() !== 'directory_listing'
            || !$this->_isContactFormEnabled($GLOBALS['sabai_entity'])
        ) return;
        
        $app = $this->_addon->getApplication();
        $form = $app->Entity_Form($app->Entity_Addon($GLOBALS['sabai_entity'])->getLeadBundleName());
        unset($form['content_parent']);
        $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME] = $app->Form_SubmitButtons(array('submit' => array(
            '#value' => $settings['btn_label'],
            '#btn_type' => 'primary',
            '#class' => $settings['btn_pos'] === 'right' ? 'sabai-pull-right' : 'sabai-pull-left',
        )));
        $form['#action'] = $app->Entity_Url($GLOBALS['sabai_entity'], '/contact');
        return $app->Form_Render($form);
    }
    
    protected function _isContactFormEnabled($entity)
    {
        $app = $this->_addon->getApplication();                
        $lead_bundle_name = $app->Entity_Addon($entity)->getLeadBundleName();
        if (!$app->HasPermission($lead_bundle_name . '_add')) return false;
                
        if (empty($entity->directory_claim)) {
            return (bool)$app->Entity_Addon($entity)->getConfig('claims', 'unclaimed', 'leads', 'enable');
        }
        if (!$app->isAddonLoaded('PaidDirectoryListings') || !$app->isAddonLoaded('PaidDirectoryListings')) return true;
        
        return ($plan = $app->PaidListings_Plan($entity))
            && (!empty($plan->features['paiddirectorylistings_leads']['enable'])
                || !empty($entity->paidlistings_plan[0]['addon_features']['paiddirectorylistings_leads']['enable']));
    }
}