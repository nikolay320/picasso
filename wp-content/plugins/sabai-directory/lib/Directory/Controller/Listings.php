<?php
class Sabai_Addon_Directory_Controller_Listings extends Sabai_Addon_Content_Controller_ListPosts
{
    protected $_template = 'directory_listings', $_sortContainer = '.sabai-directory-listings-container',
        $_center = array(), $_swne, $_settings, $_geolocate = false, $_viewport, $_geocodeError, $_filterOnChange = true;
    
    protected function _doExecute(Sabai_Context $context)
    {
        $this->_settings = $this->_getDefaultSettings($context);
        $this->_settings['map'] += array('span' => 5);
        $this->_perPage = $this->_settings['perpage'];
        $this->_defaultSort = $this->_settings['sort'];
        $this->_filter = empty($this->_settings['search']['no_filters']);
        $this->_filterOnChange = !empty($this->_settings['search']['filters_auto']);
        $this->_showFilters = !empty($this->_settings['search']['show_filters']);
        $this->_largeScreenSingleRow = empty($this->_settings['search']['filters_top']);
        // Init views
        if (!empty($this->_settings['map']['disable']) || !$this->isAddonLoaded('GoogleMaps')) {
            if (false !== $map_view_index = array_search('map', $this->_settings['views'])) {
                unset($this->_settings['views'][$map_view_index]);
            }
            $this->_settings['map']['list_show'] = false;
            if (empty($this->_settings['views'])) {
                $this->_settings['views'] = array('list');
            }
        }
        
        if ($keywords = $context->getRequest()->asStr('keywords', $this->_settings['keywords'])) {
            $this->_settings['keywords'] = $this->Keywords($keywords, $this->getAddon()->getConfig('search', 'min_keyword_len'));
        }
        $this->_settings['category'] = $context->getRequest()->asStr('category', $this->_settings['parent_category']);
        // Allow switching view mode only if views nav is enabled
        if (empty($this->_settings['hide_nav']) && empty($this->_settings['hide_nav_views'])) {
            if ((null === $view = $this->Cookie('sabai_directory_view'))
                || !in_array($view, $this->_settings['views'])
            ) {
                $this->_settings['view'] = $context->getRequest()->asStr('view', $this->_settings['view'], $this->_settings['views']);
                $this->Cookie('sabai_directory_view', $this->_settings['view']);
            } else {
                $this->_settings['view'] = $context->getRequest()->asStr('view', $view, $this->_settings['views']);
                if ($this->_settings['view'] !== $view) {
                    $this->Cookie('sabai_directory_view', $this->_settings['view']);
                }
            }
            if (count($this->_settings['views']) <= 1) {
                $this->_settings['hide_nav_views'] = true;
            }
        }
        // Make sure the default view is valid
        if (!in_array($this->_settings['view'], $this->_settings['views'])) {
            $this->_settings['view'] = current($this->_settings['views']);
        }
        // Show all listings in Map view?
        if ($this->_settings['view'] === 'map' && !empty($this->_settings['map']['map_show_all'])) {
            $this->_paginate = false;
            $this->_settings['hide_nav_sorts'] = true;
        }
        if ($context->getRequest()->has('address')) {
            $this->_settings['address'] = trim($context->getRequest()->asStr('address'));
        }
        if ($zoom = $context->getRequest()->asInt('zoom')) {
            $this->_settings['map']['listing_default_zoom'] = $zoom;
        }
        // the value of scrollwheel must be boolean so that json_encode will convert it correctly
        $this->_settings['map']['options']['scrollwheel'] = !empty($this->_settings['map']['options']['scrollwheel']);
        $this->_settings['is_mile'] = $context->getRequest()->asBool('is_mile', $this->_settings['is_mile']);
        $this->_settings['distance'] = $context->getRequest()->asInt('directory_radius', $this->_settings['distance']);
        
        // Lat/Lng specified?
        if ($center = $context->getRequest()->asStr('center', @$this->_settings['center'])) {
            if (($center_latlng = explode(',', $center))
                && count($center_latlng) === 2
            ) {
                $this->_center = array((float)$center_latlng[0], (float)$center_latlng[1]);
            }
            if ($sw = $context->getRequest()->asStr('sw')) {
                if (($sw_latlng = explode(',', $sw))
                    && count($sw_latlng) === 2
                ) {
                    if ($ne = $context->getRequest()->asStr('ne')) {
                        if (($ne_latlng = explode(',', $ne))
                            && count($ne_latlng) === 2
                        ) {
                            $this->_swne = $this->_viewport = array(
                                array((float)$sw_latlng[0], (float)$sw_latlng[1]),
                                array((float)$ne_latlng[0], (float)$ne_latlng[1])
                            );
                            $this->_settings['address'] = '';
                            $this->_settings['distance'] = 0;
                        }
                    }
                }
            }
        }
        
        try {
            // Geolocation?
            if (($is_geolocate = $context->getRequest()->asBool('is_geolocate'))
                && $this->_center
            ) {
                if (!$this->_settings['address']) {
                    $geocode = $this->GoogleMaps_Geocode(implode(',', $this->_center), true);
                    // Set address to fill the location input text field
                    $this->_settings['address'] = $geocode['address'];
                }
                $this->_geolocate = true;
                $this->_settings['map']['options']['force_fit_bounds'] = true;
            } else {
                $is_geolocate = false;
                // Fetch center lat/lng from address
                if (strlen($this->_settings['address'])) {
                    switch ($this->_settings['address_type'] = $context->getRequest()->asStr('address_type', $this->_settings['address_type'])) {
                        case 'state':
                        case 'city':
                        case 'zip':
                            break;
                        default:
                            $this->_settings['address_type'] = null;
                            if (!$this->_center || empty($this->_settings['distance'])) { 
                                $geocode = $this->GoogleMaps_Geocode($this->_settings['address']);
                                $this->_center = array($geocode['lat'], $geocode['lng']);
                                // Fetch viewport if no distance speficied
                                if (empty($this->_settings['distance']) && $geocode['viewport']) {
                                    $this->_viewport = array(array($geocode['viewport'][0], $geocode['viewport'][1]), array($geocode['viewport'][2], $geocode['viewport'][3]));
                                }
                            }
                    }
                }
                $this->_settings['map']['options']['force_fit_bounds'] = false;
            }
        } catch (Sabai_Addon_Google_GeocodeException $e) {
            if ($e->getGeocodeStatus() === 'ZERO_RESULTS') {
                $this->_geocodeError = __('Invalid location', 'sabai-directory');
            } else {
                $this->_geocodeError = $e->getMessage();
            }
        }
        
        if ($this->_center && !empty($this->_settings['distance'])) {
            // Draw circle on the map
            if (!isset($this->_settings['map']['options']['circle'])) {
                $this->_settings['map']['options']['circle'] = array();
            }
            $this->_settings['map']['options']['circle'] += array(
                'center' => $this->_center,
                'radius' => $this->_settings['distance'],
                'is_mile' => $this->_settings['is_mile']
            );
        } else {
            unset($this->_settings['map']['options']['circle']);
        }
        
        $this->_settings = $this->Filter('directory_listings_settings', $this->_settings);
        
        // Any suggested categories?
        $category_suggestions = $this->_getCategorySuggestions();
        
        parent::_doExecute($context);       
        $context->setAttributes(array(
            'settings' => $this->_settings,
            'views' => $this->_getListingViews($context),
            'center' => $this->_center,
            'is_drag' => $context->getRequest()->asBool('is_drag'),
            'is_geolocate' => $is_geolocate,
            'geocode_error' => $this->_geocodeError,
            'category_suggestions' => $category_suggestions,
        ));
        
        if ($is_ajax = $context->getRequest()->isAjax()) {
            if (strpos($is_ajax, '.sabai-directory-listings-container')) {
                $context->addTemplate(empty($this->_settings['hide_listings']) ? 'directory_listings_' . $this->_settings['view'] : 'system_nocontent');
            }
        } else {
            // Load JS files
            if ($this->isAddonLoaded('GoogleMaps')) {
                $this->GoogleMaps_LoadApi(array(
                    'map' => true,
                    'autocomplete' => true,
                    'style' => empty($this->_settings['map']['style']) ? false : $this->_settings['map']['style'],
                    'markerclusterer' => !empty($this->_settings['map']['options']['marker_clusters']),
                ));
            }
            if (!empty($this->_settings['map']['list_show'])) {
                $this->LoadJs('jquery.sticky.min.js', 'jquery-jsticky', 'jquery', 'sabai-directory');
            }
            if (empty($this->_settings['hide_searchbox'])) {
                $this->LoadJqueryUi(array('slider'));
            }
            if ((empty($this->_settings['search']['no_key']) && !empty($this->_settings['search']['auto_suggest']))
                || empty($this->_settings['search']['no_loc'])
            ) {
                $this->LoadJs('typeahead.bundle.min.js', 'twitter-typeahead', 'jquery');
            }   
            if (empty($this->_settings['no_masonry']) && in_array('grid', $this->_settings['views'])) {
                $this->LoadJqueryMasonry();
            }
        }
    }
    
    protected function _getCategorySuggestions()
    {
        if (!isset($this->_settings['keywords'][2])) return array();
        
        $query = $this->Entity_Query('taxonomy')->propertyContains('term_title', $this->_settings['keywords'][2]);
        if (is_array($this->_settings['category_bundle'])) {
            $query->propertyIsIn('term_entity_bundle_name', array_keys($this->_settings['category_bundle']));
        } else {
            $query->propertyIs('term_entity_bundle_name', $this->_settings['category_bundle']);
        }
        if (!empty($this->_settings['category'])) {
            $this->_settings['category_ids'] = array($this->_settings['category']);
            foreach ($this->Taxonomy_Descendants($this->_settings['category'], false) as $_category) {
                $this->_settings['category_ids'][] = $_category->id;
            }
            $query->propertyIsIn('term_id', $this->_settings['category_ids']);
        }
        $ret = array();
        foreach ($query->fetch() as $category) {
            $ret[] = $this->Entity_Permalink($category);
        }
        return $ret;
    }
    
    protected function _isFilterRequested(Sabai_Context $context)
    {
        if (!$context->getRequest()->asBool('filter', @$this->_settings['filter'])
            && empty($this->_settings['distance'])
        ) {
            return false;
        }
        $ret = $context->getRequest()->getParams();
        if (!empty($this->_settings['filters'])) {
            $ret += $this->_settings['filters'];
        }
        return $ret;
    }
    
    protected function _getFilterTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return '.sabai-directory-listings-container';
    }
    
    protected function _getFilterFormTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return '.sabai-directory-filters';
    }
    
    protected function _getListingViews(Sabai_Context $context)
    {
        $views = array(
            'list' => array(
                'label' => __('List', 'sabai-directory'),
                'icon' => 'th-list',
                'title' => __('Switch to list view', 'sabai-directory'),
            ),
            'grid' => array(
                'label' => __('Grid', 'sabai-directory'),
                'icon' => 'th-large',
                'title' => __('Switch to grid view', 'sabai-directory'),
            ),
            'map' => array(
                'label' => __('Map', 'sabai-directory'),
                'icon' => 'map-marker',
                'title' => __('Switch to map view', 'sabai-directory'),
            ),
        );
        $ret = array();
        $params = $context->url_params;
        if (isset($context->paginator)) {
            $params[Sabai::$p] = $context->paginator->getCurrentPage();
        } else {
            if ($this->_settings['view'] === 'map'
                && !empty($this->_settings['map']['map_show_all'])
                && ($page = $context->getRequest()->asInt(Sabai::$p))
            ) {
                $params[Sabai::$p] = $page;
            }
        }
        foreach (array_intersect_key($views, array_flip($this->_settings['views'])) as $view => $view_data) {
            $ret[$view] = $this->LinkToRemote(
                $view_data['label'],
                $context->getContainer(),
                $this->Url($context->getRoute(), array('view' => $view) + $params),
                array('target' => '.sabai-directory-listings-container', 'active' => $this->_settings['view'] === $view, 'icon' => $view_data['icon'], 'cache' => true),
                array('class' => 'sabai-directory-view', 'title' => $view_data['title'], 'data-container' => $this->_sortContainer, 'data-cookie-name' => 'sabai_directory_view', 'data-cookie-value' => $view)
            );
        }

        return $ret;
    }

    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $params = array();
        if (strlen($this->_settings['address'])) {
            $params['address'] = $this->_settings['address'];
        }
        if (isset($this->_settings['address_type'])) {
            $params['address_type'] = $this->_settings['address_type'];
        }
        if (isset($this->_settings['keywords'][2])) {
            $params['keywords'] = $this->_settings['keywords'][2];
        }
        $params['category'] = $this->_settings['category'];
        if ($this->_center) {
            $params['center'] = implode(',', $this->_center);
            if ($this->_geolocate) {
                $params['is_geolocate'] = 1;
            }
        }
        if ($this->_swne) {
            $params['sw'] = implode(',', $this->_swne[0]);
            $params['ne'] = implode(',', $this->_swne[1]);
        }
        $params['zoom'] = $this->_settings['map']['listing_default_zoom'];
        $params['is_mile'] = $this->_settings['is_mile'];
        $params['directory_radius'] = $this->_settings['distance'];
        $params['view'] = $this->_settings['view'];
        
        return $params;
    }

    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $sorts = parent::_getSorts($context, $bundle) + array(
            'reviews' => array(
                'label' => __('Most Reviews', 'sabai-directory'),
                'field_name' => 'content_children_count',
                'field_type' => 'content_children_count',
                'args' => array('child_bundle_name' => 'directory_listing_review'),
            ),
            'rating' => array(
                'label' => __('Highest Rated', 'sabai-directory'),
                'field_name' => 'voting_rating',
                'field_type' => 'voting_rating',
            ),
            'claimed' => array(
                'label' => __('Claimed', 'sabai-directory'),
                'field_name' => 'directory_claim',
                'field_type' => 'directory_claim',
            ),
            'unclaimed' => array(
                'label' => __('Unclaimed', 'sabai-directory'),
                'field_name' => 'directory_claim',
                'field_type' => 'directory_claim',
                'args' => array('asc'),
            ),
        );
        if ($this->_center) {
            $sorts['distance'] = array(
                'label' => __('Distance', 'sabai-directory'),
                'field_name' => 'directory_location',
                'field_type' => 'googlemaps_marker',
                'args' => array('lat' => $this->_center[0], 'lng' => $this->_center[1], 'is_mile' => $this->_settings['is_mile']),
            );
        }
        
        return isset($this->_settings['sorts']) ? array_intersect_key($sorts, array_flip($this->_settings['sorts'])) : $sorts;
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {        
        $query = $this->Directory_ListingsQuery(
            $this->_createListingsQuery($context, $bundle),
            $this->_center,
            $this->_settings['keywords'],
            isset($this->_settings['category_ids']) ? $this->_settings['category_ids'] : $this->_settings['category'],
            isset($this->_viewport) ? $this->_viewport : $this->_settings['distance'],
            $this->_settings['is_mile'],
            !empty($this->_settings['featured_only']),
            !empty($this->_settings['claimed_only']),
            $this->_settings['search']['match'] === 'any',
            empty($this->_settings['search']['fields']) ? null : $this->_settings['search']['fields']
        );
        // Sort by featured first?
        if ($this->_settings['category']) {
            if (!empty($this->_settings['feature_cat'])) {
                $query->sortByField('content_featured', 'DESC');
            }
        } else {
            if (!empty($this->_settings['feature'])) {
                $query->sortByField('content_featured', 'DESC');
            }
        }
        // Specific address type?
        if (isset($this->_settings['address_type'])) {
            $query->fieldIs('directory_location', $this->_settings['address'], $this->_settings['address_type']);
        }

        return $this->Filter('directory_listings_query', $query, array($bundle, $this->_settings));
    }
    
    protected function _createListingsQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle);
    }
    
    protected function _getDefaultSettings(Sabai_Context $context)
    {
        return $this->_getAddonSettings($context, $this->getAddon());
    }
    
    protected function _getAddonSettings(Sabai_Context $context, $addon)
    {
        if (!$addon instanceof Sabai_Addon) {
            $addon = $this->getAddon($addon);
        }
        $config = $addon->getConfig();
        if (!$this->isAddonLoaded('GoogleMaps')) {
            $config['search']['no_loc'] = true;
        }
        return array(
            'perpage' => $config['display']['perpage'],
            'sorts' => isset($config['display']['sorts']) ? $config['display']['sorts'] : null,
            'sort' => $config['display']['sort'],
            'view' => $config['display']['view'],
            'views' => isset($config['display']['views']) ? $config['display']['views'] : array('list', 'grid', 'map'), // for compate with < 1.3.0
            'distance' => isset($config['search']['radius']) ? $config['search']['radius'] : 0,
            'is_mile' => @$config['map']['distance_mode'] === 'mil',
            'address' => '',
            'address_type' => null,
            'keywords' => array(),
            'parent_category' => $this->_getDefaultCategoryId($context),
            'category_bundle' => $addon->getCategoryBundleName(),
            'map' => $config['map'] + array('listing_default_zoom' => 15, 'options' => array()), // for compat with < 1.2.5
            'feature' => ($stick_featured = !isset($config['display']['stick_featured']) || !empty($config['display']['stick_featured'])),  // for compat with < 1.2.17
            'feature_cat' => isset($config['display']['stick_featured_cat']) ? !empty($config['display']['stick_featured_cat']) : $stick_featured,  // for compat with < 1.2.17
            'search' => $config['search'],
            'grid_columns' => $config['display']['grid_columns'],
            'no_masonry' => !empty($config['display']['no_masonry']),
            'claimed_only' => !empty($config['claims']['unclaimed']['noindex']),
        );
    }
    
    protected function _getDefaultCategoryId(Sabai_Context $context)
    {
        return 0;
    }
}
