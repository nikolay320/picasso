<?php
class Sabai_Addon_Directory extends Sabai_Addon
    implements Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IFilters,
               Sabai_Addon_Field_IRenderers,
               Sabai_Addon_Taxonomy_ITaxonomies,
               Sabai_Addon_System_ISlugs,
               Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Content_IContentTypes,
               Sabai_Addon_System_IAdminMenus,
               Sabai_Addon_System_IUserMenus,
               Sabai_Addon_Widgets_IWidgets
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-directory';
 
    protected $_path, $_listingBundleName, $_reviewBundleName, $_leadBundleName, $_photoBundleName, $_categoryBundleName;
    
    protected function _init()
    {
        $this->_path = $this->_application->Path(dirname(__FILE__) . '/Directory');
        $this->_listingBundleName = $this->_config['listing_name'];
        $this->_reviewBundleName = $this->_config['listing_name'] . '_review';
        $this->_photoBundleName = $this->_config['listing_name'] . '_photo';
        $this->_leadBundleName = $this->_config['listing_name'] . '_lead';
        $this->_categoryBundleName = $this->_config['listing_name'] . '_category';
        
        return $this;
    }
        
    public function isCloneable()
    {
        return !$this->hasParent();
    }
    
    public function getListingBundleName()
    {
        return $this->_listingBundleName;
    }
    
    public function getReviewBundleName()
    {
        return $this->_reviewBundleName;
    }

    public function getPhotoBundleName()
    {
        return $this->_photoBundleName;
    }

    public function getLeadBundleName()
    {
        return $this->_leadBundleName;
    }

    public function getCategoryBundleName()
    {
        return $this->_categoryBundleName;
    }
    
    public function systemSlugsGetInfo()
    {
        return array('admin_route' => '/' . strtolower($this->_name) . '/settings/pages', 'admin_weight' => 35);
    }
    
    public function systemGetSlugs()
    {
        $slugs = array(
            'directory' => array(
                'admin_title' => __('Directory Index Page', 'sabai-directory'),
                'is_root' => true,
                'is_required' => true,
                'title' => $this->_name,
                'slug' => strtolower($this->_name),
            ),
            'categories' => array(
                'admin_title' => __('Category List Page', 'sabai-directory'),
                'title' => sprintf(__('%s Categories', 'sabai-directory'), $this->_name),
                'parent' => 'directory',
            ),
            'reviews' => array(
                'admin_title' => __('Review List Page', 'sabai-directory'),
                'title' => sprintf(__('%s Reviews', 'sabai-directory'), $this->_name),
                'parent' => 'directory',
            ),
            'photos' => array(
                'admin_title' => __('Photo List Page', 'sabai-directory'),
                'title' => sprintf(__('%s Photos', 'sabai-directory'), $this->_name),
                'parent' => 'directory',
            ),
            'listing' => array(
                'admin_title' => __('Single Listing Page', 'sabai-directory'),
                'title' => sprintf(__('%s Listing', 'sabai-directory'), $this->_name),
                'parent' => 'directory',
            ),
            'claim' => array(
                'admin_title' => __('Claim Listing Page', 'sabai-directory'),
            ),
        );
        if (!$this->hasParent()) {
            $slugs += array(
                'dashboard' => array(
                    'admin_title' => __('Directory Dashboard Page', 'sabai-directory'),
                    'is_root' => true,
                    'is_required' => true,
                    'title' => __('Directory Dashboard', 'sabai-directory'),
                    'slug' => 'directory-dashboard',
                ),
                'add-listing' => array(
                    'admin_title' => __('Add Listing Page', 'sabai-directory'),
                    'is_root' => true,
                    'is_required' => true,
                    'title' => __('Add Listing', 'sabai-directory'),
                    'slug' => 'add-directory-listing',
                ),
            );
        }
        
        return $slugs;
    }
    
    public function systemGetMainRoutes()
    {        
        $routes = array(
            '/' . $this->getSlug('directory') => array(
                'controller' => 'Listings',
                'access_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'posts',
                'title_callback' => true,
                'data' => array(
                    'bundle_name' => $this->_listingBundleName,
                ),
                'controller_addon' => 'Directory',
                'type' => Sabai::ROUTE_TAB,
                'priority' => 5,
            ),
            '/' . $this->getSlug('directory') . '/feed' => array(
                'controller' => 'Feed',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/' . $this->getSlug('directory') . '/users/:user_name' => array(
                'callback_path' => 'user_listings',
                'controller' => 'UserListings',
                'access_callback' => true,
                'title_callback' => true,
                'format' => array(':user_name' => '.+'),
                'controller_addon' => 'Directory',
                'data' => array('clear_tabs' => true),
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
            ),
            '/' . $this->getSlug('directory') . '/users/:user_name/reviews' => array(
                'callback_path' => 'user_reviews',
                'controller' => 'UserReviews',
                'access_callback' => true,
                'title_callback' => true,
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 5,
            ),
            '/' . $this->getSlug('directory') . '/users/:user_name/photos' => array(
                'controller' => 'UserPhotos',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'user_photos',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'weight' => 10,
                'ajax' => false,
            ),
            '/' . $this->getSlug('directory') . '/reviews' => array(
                'controller' => 'Reviews',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'child_posts',
                'callback_addon' => 'Content',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'data' => array(
                    'bundle_name' => $this->_reviewBundleName,
                ),
            ),
            '/' . $this->getSlug('directory') . '/photos' => array(
                'controller' => 'Photos',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'child_posts',
                'callback_addon' => 'Content',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'data' => array(
                    'bundle_name' => $this->_photoBundleName,
                ),
            ),
            '/' . $this->getSlug('directory') . '/reviews/:entity_id' => array(
                'controller' => 'RedirectToListing',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'child_post',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getSlug('directory') . '/photos/:entity_id' => array(
                'controller' => 'RedirectToListing',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => 'Content',
                'callback_path' => 'child_post',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getSlug('listing') . '/:slug/edit' => array(
                'controller' => 'EditListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'edit_listing',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getSlug('listing') . '/:slug/delete' => array(
                'controller' => 'TrashListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'delete_listing',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getSlug('listing') . '/:slug/reviews' => array(
                'controller' => 'ListingReviews',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_reviews',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => 6,
            ),
             '/' . $this->getSlug('listing') . '/:slug/photos' => array(
                'controller' => 'ListingPhotos',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_photos',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => 10,
            ),
            '/' . $this->getSlug('listing') . '/:slug/map' => array(
                'controller' => 'ListingMap',
                'title_callback' => true,
                'access_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_map',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => 50,
            ),
            '/' . $this->getSlug('listing') . '/:slug/contact' => array(
                'controller' => 'ListingContact',
                'title_callback' => true,
                'access_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'listing_contact',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => 60,
            ),
            '/' . $this->getSlug('listing') . '/:slug/related' => array(
                'controller' => 'RelatedListings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'related_listings',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => 70,
            ),
            '/' . $this->getSlug('listing') . '/:slug/nearby' => array(
                'controller' => 'NearbyListings',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_path' => 'nearby_listings',
                'controller_addon' => 'Directory',
                'priority' => 5,
                'weight' => 70,
            ),
            '/' . $this->getSlug('listing') . '/:slug/photos/add' => array(
                'controller' => 'UploadPhotos',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'upload_photos',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getSlug('listing') . '/:slug/ratings' => array(
                'controller' => 'ListingRatings',
                'title_callback' => true,
                'callback_path' => 'listing_ratings',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getSlug('listing') . '/:slug/' . $this->getSlug('claim') => array(
                'controller' => 'ClaimListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'claim',
                'controller_addon' => 'Directory',
                'priority' => 5,
            ),
            '/' . $this->getSlug('categories') . '/:slug/listings' => array(
                'controller' => 'TermListings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'weight' => 1,
                'controller_addon' => 'Directory',
                'callback_path' => 'term_listings',
                'priority' => 5,
            ),
            '/' . $this->getSlug('directory') . '/add' => array(
                'forward' => $this->hasParent() ? $this->_application->getAddon('Directory')->getSlug('add-listing') : '/' . $this->getSlug('add-listing'),
            ),
        );
        if (!$this->hasParent()) {
            $routes += array(
                '/' . $this->getSlug('dashboard') => array(
                    'controller' => 'Dashboard',
                    'title' => $this->getTitle('dashboard'),
                    'access_callback' => true,
                    'title_callback' => true,
                    'callback_path' => 'dashboard',
                    'priority' => 5,
                ),
                '/' . $this->getSlug('dashboard') . '/leads' => array(
                    'controller' => 'Leads',
                    'title_callback' => true,
                    'access_callback' => true,
                    'callback_path' => 'leads',
                    'priority' => 5,
                    //'type' => Sabai::ROUTE_TAB,
                ),
                '/' . $this->getSlug('add-listing') => array(
                    'controller' => 'AddListing',
                    'callback_path' => 'add_listing',
                    'title_callback' => true,
                    'weight' => 30,
                    'priority' => 5,
                ),
                '/sabai/directory' => array(
                    'controller' => 'AllListings',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'access_callback' => true,
                    'callback_path' => 'directory',
                    'priority' => 5,
                ),
                '/sabai/directory/map' => array(
                    'controller' => 'Map',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/categories' => array(
                    'controller' => 'AllCategories',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/reviews' => array(
                    'controller' => 'AllReviews',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/photos' => array(
                    'controller' => 'AllPhotos',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/geolocate' => array(
                    'controller' => 'GeoLocate',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/slider' => array(
                    'controller' => 'Slider',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/feed' => array(
                    'controller' => 'AllFeeds',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/searchform' => array(
                    'controller' => 'SearchForm',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/listing/:listing_id' => array(
                    'format' => array(':listing_id' => '\d+'),
                    'controller' => 'Listing',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'access_callback' => true,
                    'callback_path' => 'listing',
                    'controller_addon' => 'Directory',
                    'priority' => 5,
                ),
                '/sabai/directory/listinglist' => array(
                    'controller' => 'ListingList',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 5,
                ),
                '/sabai/directory/locationlist' => array(
                    'controller' => 'LocationList',
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
                return true;
            case 'user_listings':
                $user_name = $context->getRequest()->asStr('user_name');
                $context->identity = $this->_application->UserIdentityByUsername(rawurldecode($user_name));
                if ($context->identity->isAnonymous()) {
                    return false;
                }
                $context->setTitle(sprintf(__('Posts by %s', 'sabai-directory'), $context->identity->name));
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                return ($context->bundle = $this->_application->Entity_Bundle($this->_listingBundleName)) ? true : false;
            case 'listing_map':
                if (!empty($this->_config['map']['disable']) || !$this->_application->isAddonLoaded('GoogleMaps')) return false;
                $lat = $context->entity->getSingleFieldValue('directory_location', 'lat');
                return !empty($lat);
            case 'user_reviews':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_reviewBundleName)) ? true : false;
            case 'listing_reviews':
                if (empty($context->entity->directory_claim)) {
                    if (isset($this->_config['claims']['unclaimed']['reviews']['enable'])
                        && empty($this->_config['claims']['unclaimed']['reviews']['enable'])) return false;
                }
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_reviewBundleName)) ? true : false;
            case 'listing_photos':
            case 'user_photos':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                return ($context->child_bundle = $this->_application->Entity_Bundle($this->_photoBundleName)) ? true : false;
            case 'listing':
                if ((!$id = $context->getRequest()->asInt('listing_id'))
                    || (!$context->entity = $this->_application->Entity_Entity('content', $id, false))
                    || !$context->entity->isPublished()
                ) {
                    return false;
                }
                $this->_application->Entity_LoadFields($context->entity);
                return true;
            case 'dashboard':
                if ($this->_application->getUser()->isAnonymous()) {
                    $context->setUnauthorizedError($route['path']);
                    return false;
                }
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir('sabai-directory') . '/templates');
                return true;
            case 'leads':
                if ($accessType !== Sabai::ROUTE_ACCESS_LINK) return true;
                return $this->_application->Entity_Query('content')
                    ->propertyIs('post_entity_bundle_type', 'directory_listing')
                    ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                    ->fieldIs('directory_claim', $this->_application->getUser()->id, 'claimed_by')
                    ->count() > 0;
            case 'claim':
                return empty($context->entity->directory_claim) && $this->_userCanClaim();
            case 'edit_listing':
                // If the listing is already claimed, make sure the user is the current listing owner
                if (!empty($context->entity->directory_claim)) {
                    return $this->_application->Directory_IsListingOwner($context->entity, true)
                        || $this->_application->IsAdministrator();
                }
                return $this->_application->HasPermission($this->_listingBundleName . '_edit_any')
                    || ($this->_application->HasPermission($this->_listingBundleName . '_edit_own') && $this->_application->Entity_IsAuthor($context->entity));
             case 'delete_listing':
                // If the listing is already claimed, make sure the user is the current listing owner
                if (!empty($context->entity->directory_claim)) {
                    return $this->_application->Directory_IsListingOwner($context->entity, true)
                        || $this->_application->IsAdministrator();
                }
                return $this->_application->HasPermission($this->_listingBundleName . '_manage')
                    || ($this->_application->HasPermission($this->_listingBundleName . '_trash_own') && $this->_application->Entity_IsAuthor($context->entity));
            case 'directory':
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir('sabai-directory') . '/templates');
                return true;
            case 'listing_tab':
                $tab = basename($route['path']);
                if (empty($context->entity->directory_claim)
                    && isset($this->_config['claims']['unclaimed']['tabs'])
                    && !in_array($tab, $this->_config['claims']['unclaimed']['tabs'])
                ) return false;
                $context->tab_name = $tab;
                return true;
            case 'listing_contact':
                if (empty($context->entity->directory_claim) && empty($this->_config['claims']['unclaimed']['leads']['enable'])) return;
                
                return $this->_application->HasPermission($this->_leadBundleName . '_add')
                    && ($context->child_bundle = $this->_application->Entity_Bundle($this->_leadBundleName));
            case 'nearby_listings':
                return isset($context->entity->directory_location[0]);
            case 'upload_photos':
                return !$this->_application->getUser()->isAnonymous() && 
                    $this->_application->HasPermission($context->child_bundle->name . '_add');
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'posts':
                return $titleType === Sabai::ROUTE_TITLE_TAB || $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? _x('Listings', 'tab', 'sabai-directory')
                    : $this->getTitle('directory');
            case 'term_listings':
                return $titleType === Sabai::ROUTE_TITLE_TAB || $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT
                    ? _x('Listings', 'tab', 'sabai-directory')
                    : $context->entity->getTitle();
            case 'listing_reviews':
                if (!strlen($title)) $title = _x('Reviews', 'tab', 'sabai-directory');
                if ($titleType !== Sabai::ROUTE_TITLE_TAB && $titleType !== Sabai::ROUTE_TITLE_TAB_DEFAULT) {
                    return $title;
                }
                return ($count = $context->entity->getSingleFieldValue('content_children_count', 'directory_listing_review'))
                    ? sprintf(_x('%s (%d)', 'tab', 'sabai-directory'), $title, $count)
                    : $title;
            case 'listing_photos':
                if (!strlen($title)) $title = _x('Photos', 'tab', 'sabai-directory');
                if ($titleType !== Sabai::ROUTE_TITLE_TAB && $titleType !== Sabai::ROUTE_TITLE_TAB_DEFAULT) {
                    return $title;
                }
                return ($count = $context->entity->getSingleFieldValue('content_children_count', 'directory_listing_photo'))
                    ? sprintf(_x('%s (%d)', 'tab', 'sabai-directory'), $title, $count)
                    : $title;
            case 'listing_map':
                return strlen($title) ? $title : __('Map', 'sabai-directory');
            case 'upload_my_listing_photos':
                return sprintf(__('Add Photos - %s', 'sabai-directory'), $context->entity->getTitle());
            case 'user_listings':
                return _x('Listings', 'tab', 'sabai-directory');
            case 'user_reviews':
                return _x('Reviews', 'tab', 'sabai-directory');
            case 'user_photos':
                return _x('Photos', 'tab', 'sabai-directory');
            case 'claim':
                return __('Claim Listing', 'sabai-directory');
            case 'edit_listing':
                return __('Edit Listing', 'sabai-directory');
            case 'delete_listing':
                return __('Delete Listing', 'sabai-directory');
            case 'dashboard':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? __('Listings', 'sabai-directory') : $title;
            case 'listing_contact':
                return strlen($title) ? $title : __('Contact Us', 'sabai-directory');
            case 'related_listings':
                return strlen($title) ? $title : __('Related Listings', 'sabai-directory');
            case 'nearby_listings':
                return strlen($title) ? $title : __('Nearby Listings', 'sabai-directory');
            case 'leads':
                return _x('Messages', 'dashboard', 'sabai-directory');
            case 'listing_ratings':
                return __('Rating Details', 'sabai-directory');
            case 'upload_photos':
                return __('Upload Photos', 'sabai-directory');
            case 'add_listing':
                return $this->getTitle('add-listing');
        }
    }

    public function systemGetAdminRoutes()
    {
        return array(
            '/' . strtolower($this->_name) . '/claims' => array(
                'controller' => 'ListingClaims',
                'title_callback' => true,
                'controller_addon' => 'Directory',
                'callback_path' => 'claims',
                'type' => Sabai::ROUTE_TAB,
                'priority' => 5,
            ),
            '/' . strtolower($this->_name) . '/claims/:claim_id' => array(
                'controller' => 'ViewListingClaim',
                'format' => array(':claim_id' => '\d+'),
                'title_callback' => true,
                'access_callback' => true,
                'controller_addon' => 'Directory',
                'callback_path' => 'claim',
                'priority' => 5,
            ),
            '/' . strtolower($this->_name) . '/settings' => array(
                'controller' => 'Settings',
                'title_callback' => true,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings',
                'data' => array('clear_tabs' => true),
            ),
            '/' . strtolower($this->_name) . '/settings/map' => array(
                'controller' => 'MapSettings',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_map',
                'weight' => 5,
            ),
            '/' . strtolower($this->_name) . '/settings/search' => array(
                'controller' => 'SearchSettings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_search',
                'weight' => 10,
            ),
            '/' . strtolower($this->_name) . '/settings/acl' => array(
                'controller' => 'AccessControlSettings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_acl',
                'weight' => 15,
            ),
            '/' . strtolower($this->_name) . '/settings/claims' => array(
                'controller' => 'ClaimsSettings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_claims',
                'weight' => 20,
            ),
            '/' . strtolower($this->_name) . '/settings/spam' => array(
                'controller' => 'SpamSettings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_spam',
                'weight' => 25,
            ),
            '/' . strtolower($this->_name) . '/settings/emails' => array(
                'controller' => 'EmailsSettings',
                'title_callback' => true,
                'type' => Sabai::ROUTE_TAB,
                'controller_addon' => 'Directory',
                'callback_path' => 'settings_emails',
                'weight' => 30,
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'settings':
                return true;
            case 'settings_map':
                return $this->_application->isAddonLoaded('GoogleMaps');
            case 'claim':
                return (($id = $context->getRequest()->asInt('claim_id'))
                    && ($context->claim = $this->getModel('Claim')->fetchById($id)));
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'claims':
                return __('Claims', 'sabai-directory');
            case 'claim':
                return $context->claim->getLabel();
            case 'settings':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? __('General', 'sabai-directory') : sprintf(_x('%s Settings', 'Settings page title', 'sabai-directory'), $this->_name);
            case 'settings_acl':
                return __('Access Control', 'sabai-directory');
            case 'settings_emails':
                return __('Emails', 'sabai-directory');
            case 'settings_map':
                return _x('Map', 'settings', 'sabai-directory');
            case 'settings_search':
                return _x('Search', 'settings', 'sabai-directory');
            case 'settings_claims':
                return _x('Claims', 'settings', 'sabai-directory');
            case 'settings_spam':
                return __('Spam', 'sabai-directory');
        }
    }

    public function contentGetContentTypeNames()
    {
        return array($this->_listingBundleName, $this->_reviewBundleName, $this->_photoBundleName, $this->_leadBundleName);
    }

    public function contentGetContentType($name)
    {
        require_once $this->_path . '/ContentType.php';
        return new Sabai_Addon_Directory_ContentType($this, $name);
    }
    
    public function getDefaultConfig()
    {
        return array(
            'display' => array(
                'perpage' => 20,
                'review_perpage' => 10,
                'photo_perpage' => 20,
                'sort' => 'newest',
                'review_sort' => 'helpfulness',
                'photo_sort' => 'votes',
                'view' => 'list',
                'grid_columns' => 4,
                'listing_tabs' => null,
                'category_columns' => 2,
                'category_hide_empty' => false,
                'category_hide_children' => false,
                'no_photo_comments' => false,
                'stick_featured' => true,
                'stick_featured_cat' => true,
            ),
            'map' => array(
                'disable' => false,
                'height' => 600,
                'list_show' => true,
                'list_scroll' => true,
                'list_height' => 600,
                'span' => 5,
                'icon' => null,
                'style' => '',
                'listing_default_zoom' => 15,
                'distance_mode' => 'km',                
                'options' => array(
                    'marker_clusters' => true,
                    'scrollwheel' => false
                ),
                'type' => 'ROADMAP',
            ),
            'search' => array(
                'no_key' => false,
                'min_keyword_len' => 3,
                'match' => 'all',
                'auto_suggest' => true,
                'suggest_listing' => true,
                'suggest_listing_jump' => true,
                'suggest_cat' => false,
                'suggest_cat_jump' => true,
                'suggest_cat_icon' => 'folder-open',
                'no_loc' => false,
                'country' => null,
                'radius' => 0,
                'no_cat' => false,
                'cat_depth' => 2,
                'cat_hide_empty' => false,
                'cat_hide_count' => false,
                'no_filters' => false,
                'filters_top' => false,
                'filters_auto' => true,
                'form_type' => null,
            ),
            'photo' => array(
                'max_file_size' => 2048,
            ),
            'claims' => array(
                'duration' => 365,
                'grace_period' => 7,
                'no_comment' => false,
                'allow_existing' => true,
                'process' => array(
                    'auto_approve_new' => true,
                    'auto_approve' => false,
                    'delete_auto_approved' => true,
                ),
                'unclaimed' => array('noindex' => false),
            ),
            'spam' => array(
                'threshold' => array('listing' => 30, 'review' => 15, 'photo' => 15),
                'auto_delete' => true,
                'delete_after' => 7,
            ),
            'listing_name' => strtolower($this->_name) . '_listing',
        );
    }
    
    public function getListingDefaultTabs()
    {
        $tabs = array(
            'reviews' => __('Reviews', 'sabai-directory'),
            'photos' => __('Photos', 'sabai-directory'),
            'map' => __('Map', 'sabai-directory'),
            'contact' => __('Contact Us', 'sabai-directory'),
            'related' => __('Related Listings', 'sabai-directory'),
            'nearby' => __('Nearby Listings', 'sabai-directory'),
            'sample' => __('Custom Tab Sample', 'sabai-directory'),
        );
        $path = $this->_application->Entity_Bundle($this->_listingBundleName)->info['permalink_path'] . '/:slug/';
        $routes = $this->_application->getModel('Route', 'System')
            ->type_is(Sabai::ROUTE_INLINE_TAB)
            ->path_startsWith($path)
            ->fetch(0, 0, 'weight', 'ASC');
        foreach ($routes as $route) {
            $tab_name = str_replace($path, '', $route->path);
            if (isset($tabs[$tab_name])
                || strpos($tab_name, '/')
            ) {
                continue;
            }
            $tabs[$tab_name] = $this->_application->Translate($route->title);
        }
        if (!$this->_application->isAddonLoaded('GoogleMaps')) {
            unset($tabs['map']);
        }
        return $tabs;
    }
    
    public function widgetsGetWidgetNames()
    {
        if ($this->hasParent()) {
            return array();
        }
        return array('directory_recent', 'directory_recent_reviews', 'directory_categories', 'directory_recent_photos',
            'directory_submitbtn', 'directory_featured', 'directory_related', 'directory_listing_map', 'directory_listing_photos',
            'directory_contact', 'directory_nearby'
        );
    }
    
    public function widgetsGetWidget($name)
    {
        require_once $this->_path . '/Widget.php';
        return new Sabai_Addon_Directory_Widget($this, substr($name, strlen($this->_name . '_')));
    }
    
    public function systemGetUserMenus()
    {
        return $this->hasParent() ? array() : array(
            strtolower($this->_name) => array(
                'title' => $this->getTitle('dashboard'),
                'url' => $this->_application->MainUrl('/' . $this->getSlug('dashboard')),
            ),
        );
    }
    
    public function systemGetAdminMenus()
    {
        $icon_path = str_replace($this->_application->getPlatform()->getSiteUrl() . '/', '', $this->_application->getPlatform()->getAssetsUrl('sabai-directory'));
        return array(
            '/' . strtolower($this->_name) => array(
                'label' => $this->_name,
                'title' => __('Listings', 'sabai-directory'),
                'icon' => $icon_path . '/images/icon.png',
                'icon_dark' => $icon_path . '/images/icon_dark.png',
            ),
            '/' . strtolower($this->_name) . '/add' => array(
                'title' => __('Add Listing', 'sabai-directory'),
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/reviews' => array(
                'title' => __('Reviews', 'sabai-directory'),
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/photos' => array(
                'title' => __('Photos', 'sabai-directory'),
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/leads' => array(
                'title' => __('Leads', 'sabai-directory'),
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/categories' => array(
                'title' => __('Categories', 'sabai-directory'),
                'parent' => '/' . strtolower($this->_name),
            ),
            '/' . strtolower($this->_name) . '/settings' => array(
                'title' => __('Settings', 'sabai-directory'),
                'parent' => '/' . strtolower($this->_name),
                'weight' => 99,
            ),
        );
    }

    public function taxonomyGetTaxonomyNames()
    {
        return array($this->_categoryBundleName);
    }

    public function taxonomyGetTaxonomy($name)
    {
        require_once $this->_path . '/Taxonomy.php';
        return new Sabai_Addon_Directory_Taxonomy($this, $name);
    }

    public function fieldGetTypeNames()
    {
        return array('directory_contact', 'directory_rating', 'directory_social', 'directory_claim', 'directory_photos', 'directory_photo');
    }

    public function fieldGetType($name)
    {
        switch ($name) {
            case 'directory_rating':
                require_once $this->_path . '/RatingFieldType.php';
                return new Sabai_Addon_Directory_RatingFieldType($this, $name);
            case 'directory_claim':
                require_once $this->_path . '/ClaimFieldType.php';
                return new Sabai_Addon_Directory_ClaimFieldType($this, $name);
            default:
                require_once $this->_path . '/FieldType.php';
                return new Sabai_Addon_Directory_FieldType($this, $name);
        }
    }

    public function fieldGetWidgetNames()
    {
        return array('directory_contact', 'directory_rating', 'directory_social', 'directory_claim', 'directory_photos');
    }

    public function fieldGetWidget($name)
    {
        require_once $this->_path . '/FieldWidget.php';
        return new Sabai_Addon_Directory_FieldWidget($this, $name);
    }

    public function fieldGetFilterNames()
    {
        return array('directory_claim', 'directory_rating');
    }

    public function fieldGetFilter($name)
    {
        switch ($name) {
            case 'directory_claim':
                require_once $this->_path . '/ClaimFieldFilter.php';
                return new Sabai_Addon_Directory_ClaimFieldFilter($this, $name);
            case 'directory_rating':
                require_once $this->_path . '/RatingFieldFilter.php';
                return new Sabai_Addon_Directory_RatingFieldFilter($this, $name);
        }
    }
    
    public function fieldGetRendererNames()
    {
        return array('directory_contact', 'directory_social', 'directory_rating', 'directory_photos', 'directory_carousel');
    }

    public function fieldGetRenderer($name)
    {
        switch ($name) {
            case 'directory_contact':
                require_once $this->_path . '/ContactFieldRenderer.php';
                return new Sabai_Addon_Directory_ContactFieldRenderer($this, $name);
            case 'directory_social':
                require_once $this->_path . '/SocialFieldRenderer.php';
                return new Sabai_Addon_Directory_SocialFieldRenderer($this, $name);
            case 'directory_rating':
                require_once $this->_path . '/RatingFieldRenderer.php';
                return new Sabai_Addon_Directory_RatingFieldRenderer($this, $name);
            case 'directory_photos':
                require_once $this->_path . '/PhotosFieldRenderer.php';
                return new Sabai_Addon_Directory_PhotosFieldRenderer($this, $name);
            case 'directory_carousel':
                require_once $this->_path . '/CarouselFieldRenderer.php';
                return new Sabai_Addon_Directory_CarouselFieldRenderer($this, $name);
        }
    }
            
    public function hasSettingsPage($currentVersion)
    {
        return '/' . strtolower($this->_name) . '/settings';
    }
    
    public function onEntityRenderEntities($bundle, $entities, $displayMode)
    {
        if ($bundle->entitytype_name !== 'content') {
            return;
        }
        
        if ($bundle->name === $this->_listingBundleName) {
            // Fetch listing photos
            $photos = $this->_application->Entity_Query()
                ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->fieldIsIn('content_parent', array_keys($entities))
                ->fieldIsNotNull('directory_photo', 'official') // official photos
                ->sortByField('directory_photo', 'ASC', 'display_order')
                ->fetch();
            foreach ($photos as $photo) {
                if (($listing = $photo->getSingleFieldValue('content_parent'))
                    && is_object($entities[$listing->getId()])
                ) {
                    $entities[$listing->getId()]->addFieldValue('directory_photos', $photo);
                }
            }
        } elseif ($bundle->name === $this->_reviewBundleName) {
            // Fetch listing photos
            $photos = $this->_application->Entity_Query()
                ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->fieldIsIn('content_reference', array_keys($entities))
                ->sortByProperty('post_id', 'ASC')
                ->fetch();
            foreach ($photos as $photo) {
                if (($review = $photo->getSingleFieldValue('content_reference'))
                    && is_object($entities[$review->getId()])
                ) {
                    $entities[$review->getId()]->addFieldValue('directory_photos', $photo);
                }
            }
            
            // Load listings for each review if in summary display mode
            if ($displayMode !== 'full') {
                $listing_ids = $review_ids = array();
                foreach ($entities as $entity) {
                    if ($listing = $entity->getSingleFieldValue('content_parent')) {
                        $listing_ids[] = $listing->getId();
                        $review_ids[$listing->getId()][] = $entity->getId();
                    }
                }
                if (!empty($listing_ids)) {
                    // Fetch listing photos
                    $listing_photos = $this->_application->Entity_Query()
                        ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                        ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                        ->fieldIsIn('content_parent', $listing_ids)
                        ->fieldIsNotNull('directory_photo', 'official') // official photos
                        ->sortByField('directory_photo', 'ASC', 'display_order')
                        ->fetch();
                    foreach ($listing_photos as $photo) {
                        if ($listing = $photo->getSingleFieldValue('content_parent')) {
                            foreach ($review_ids[$listing->getId()] as $review_id) {
                                if (is_object($entities[$review_id])) {
                                    $entities[$review_id]->data['directory_listing_photos'][] = $photo;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    protected function _userCanClaim()
    {
        return ($this->_application->getUser()->isAdministrator()
            || ((!isset($this->_config['claims']['allow_existing']) || $this->_config['claims']['allow_existing'])
                && ($this->_application->getUser()->isAnonymous() || $this->_application->HasPermission($this->_listingBundleName . '_claim')))
        );
    }
    
    public function onEntityRenderContentDirectoryListingHtml($bundle, $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        if ($priority = $entity->isFeatured()) {
            $classes[] = 'sabai-directory-listing-featured';
            $classes[] = 'sabai-directory-listing-featured-' . $priority;
        }
        
        if ($claim = $entity->getSingleFieldValue('directory_claim')) {
            $entity->data['entity_labels']['directory_claimed'] = array(
                'label' => __('Verified', 'sabai-directory'),
                'title' => __('This is an owner verified listing.', 'sabai-directory'),
                'icon' => 'check-circle',
            );
            // Add title icon
            $entity->data['entity_icons']['directory_claimed'] = array(
                'icon' => 'check-circle',
                'title' => __('This is an owner verified listing.', 'sabai-directory'),
            );
            $classes[] = 'sabai-directory-listing-claimed';
            if ($displayMode === 'full') {
                // Add link to edit my listing page if the user has claimed this listing
                if ($claim['claimed_by'] == $this->_application->getUser()->id) {
                    if (empty($claim['expires_at']) || $claim['expires_at'] > time()) {
                        $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
                    }
                    if ($this->_application->HasPermission($this->_listingBundleName . '_trash_own_claimed')) {
                        $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete'), array('icon' => 'trash-o', 'width' => 470), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
                    }
                } elseif ($this->_application->IsAdministrator()) {
                    $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
                    $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete'), array('icon' => 'trash-o', 'width' => 470), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
                }
            }
            
            return;
        }
        
        if ($displayMode === 'full') {
            if ($this->_application->HasPermission($this->_listingBundleName . '_edit_any')
                || ($this->_application->HasPermission($this->_listingBundleName . '_edit_own') && $this->_application->Entity_IsAuthor($entity, $this->_application->getUser()))
            ) {
                $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
            }
            if ($this->_application->HasPermission($this->_listingBundleName . '_manage')
                || ($this->_application->HasPermission($this->_listingBundleName . '_trash_own') && $this->_application->Entity_IsAuthor($entity, $this->_application->getUser()))
            ) {
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete'), array('icon' => 'trash-o', 'width' => 470), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
            }
            
            if ($this->_userCanClaim()) {
                $buttons['links']['claim'] = $this->_application->LinkTo(
                    sprintf(__('Claim this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true)),
                    $this->_application->Entity_Url($entity, '/' . $this->getSlug('claim')),
                    array('icon' => 'check'),
                    array('class' => 'sabai-btn-warning sabai-directory-btn-claim')
                );
            }
        }
    }
    
    public function onEntityRenderContentDirectoryListingReviewHtml($bundle, $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if ($displayMode === 'preview') return;

        if ($entity->isFeatured()) {
            $classes[] = 'sabai-directory-listing-featured';
        }
        
        if ($displayMode === 'full') {
            $user = $this->_application->getUser();
            $bundle_label_singular = $this->_application->Entity_BundleLabel($bundle, true);
            $can_manage = $this->_application->HasPermission($this->_reviewBundleName . '_manage');
            if ($can_manage) {
                $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $bundle_label_singular)));
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete', array('delete_target_id' => $id)), array('width' => 470, 'icon' => 'trash-o'), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $bundle_label_singular)));
            } else { 
                $is_author = $this->_application->Entity_IsAuthor($entity, $user);
                if ($this->_application->HasPermission($this->_reviewBundleName . '_edit_any')
                    || ($is_author && $this->_application->HasPermission($this->_reviewBundleName . '_edit_own'))
                ) {
                    $links['edit'] = $this->_application->LinkTo(__('Edit', 'sabai-directory'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai-directory'), $bundle_label_singular)));
                }
                if ($this->isReviewTrashable($entity, $user)) {
                    $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete', array('delete_target_id' => $id)), array('width' => 470, 'icon' => 'trash-o'), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $bundle_label_singular)));
                }
            }
        }
    }
    
    public function onEntityRenderContentDirectoryListingPhotoHtml($bundle, $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if ($bundle->name !== $this->_photoBundleName) return;
        
        if ($entity->directory_photo[0]['official'] == 1) {
            $entity->data['entity_labels']['directory_official'] = array(
                'label' => __('Verified', 'sabai-directory'),
                'title' => $title = __('This is a photo uploaded by the listing owner.', 'sabai-directory'),
                'icon' => 'check-circle',
            );
            // Add title icon
            $entity->data['entity_icons']['directory_official'] = array(
                'icon' => 'check-circle',
                'title' => $title,
            );
            $classes[] = 'sabai-directory-photo-official';
        } else {
            if ($this->_application->HasPermission($this->_photoBundleName . '_manage')) {
                $links['delete'] = $this->_application->LinkToModal(__('Delete', 'sabai-directory'), $this->_application->Entity_Url($entity, '/delete'), array('width' => 470, 'icon' => 'trash-o'), array('title' => sprintf(__('Delete this %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($bundle, true))));
            }
        }
    }
    
    public function onEntityCreateContentDirectoryListingEntity($bundle, &$values)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        // Initialize review/lead count
        $values['content_children_count'][] = array('value' => 0, 'child_bundle_name' => 'directory_listing_review');
        $values['content_children_count'][] = array('value' => 0, 'child_bundle_name' => 'directory_listing_lead');
    }
    
    public function onFormBuildContentAdminListposts(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_listingBundleName) {
            return;
        }
        $form['entities']['#header']['reviews'] = array(
            'order' => 12,
            'label' => __('Reviews', 'sabai-directory'),
        );
        $form['entities']['#header']['photos'] = array(
            'order' => 14,
            'label' => '<i class="fa fa-camera" title="'. __('Photos', 'sabai-directory') .'">',
        );
        $form['entities']['#header']['leads'] = array(
            'order' => 13,
            'label' => __('Leads', 'sabai-directory'),
        );
        $form['entities']['#header']['owner'] = array(
            'order' => 2,
            'label' => __('Owner', 'sabai-directory'),
        );
    
        if (!empty($form['entities']['#options'])) {
            $pending_counts = array();
            foreach (array($this->_reviewBundleName, $this->_photoBundleName, $this->_leadBundleName) as $bundle_name) {
                $pending_counts[$bundle_name] = $this->_application->Entity_Query('content')
                    ->propertyIs('post_entity_bundle_name', $bundle_name)
                    ->propertyIsIn('post_status', array(Sabai_Addon_Content::POST_STATUS_PENDING, Sabai_Addon_Content::POST_STATUS_DRAFT))
                    ->fieldIsIn('content_parent', array_keys($form['entities']['#options']))
                    ->groupByField('content_parent')
                    ->count();
            }
            $form['entities']['#header']['author']['order'] = 3;
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                $icons = array();
                if ($claim = $entity->getSingleFieldValue('directory_claim')) {
                    $icons[] = '<i class="fa fa-check-circle sabai-entity-icon-directory-claimed"></i>';
                    $form['entities']['#options'][$entity_id]['owner'] = $this->_application->UserIdentityLink($this->_application->UserIdentity($claim['claimed_by']));
                }
                if ($entity->isFeatured()) {
                    $icons[] = '<i class="fa fa-certificate sabai-entity-icon-featured"></i>';
                }
                $form['entities']['#options'][$entity_id]['title'] = '<span class="sabai-directory-icons">' . implode(PHP_EOL, $icons) . '</span> ' . $form['entities']['#options'][$entity_id]['title'];
                $review_count = (int)$entity->getSingleFieldValue('content_children_count', 'directory_listing_review');
                $photo_count = (int)$entity->getSingleFieldValue('content_children_count', 'directory_listing_photo');
                $lead_count = (int)$entity->getSingleFieldValue('content_children_count', 'directory_listing_lead');
                $form['entities']['#options'][$entity_id] += array(
                    'reviews' => $this->_application->LinkTo(
                        empty($pending_counts[$this->_reviewBundleName][$entity_id]) ? $review_count : sprintf('%d (%d)', $review_count, $pending_counts[$this->_reviewBundleName][$entity_id]),
                        $this->_application->Url('/' . strtolower($this->_name) . '/reviews', array('content_parent' => $entity_id))
                    ),
                    'photos' => $this->_application->LinkTo(
                        empty($pending_counts[$this->_photoBundleName][$entity_id]) ? $photo_count : sprintf('%d (%d)', $photo_count, $pending_counts[$this->_photoBundleName][$entity_id]),
                        $this->_application->Url('/' . strtolower($this->_name) . '/photos', array('content_parent' => $entity_id))
                    ),
                    'leads' => $this->_application->LinkTo(
                        empty($pending_counts[$this->_leadBundleName][$entity_id]) ? $lead_count : sprintf('%d (%d)', $lead_count, $pending_counts[$this->_leadBundleName][$entity_id]),
                        $this->_application->Url('/' . strtolower($this->_name) . '/leads', array('content_parent' => $entity_id))
                    ),
                );
            }
        }
            
        $form['#filters']['directory_claim'] = array(
            'order' => 5,
            'default_option_label' => sprintf(__('Claimed/Unclaimed', 'sabai-directory')),
            'options' => array(1 => __('Claimed', 'sabai-directory'), 2 => __('Unclaimed', 'sabai-directory'), 3 => __('Claim expired', 'sabai-directory'), 4 => __('Claim expiring (7 days)', 'sabai-directory'), 5 => __('Claim expiring (30 days)', 'sabai-directory')),
        );
        $form['#filters']['directory_featured'] = array(
            'order' => 6,
            'default_option_label' => sprintf(__('Featured/Unfeatured', 'sabai-directory')),
            'options' => array(1 => __('Featured', 'sabai-directory'), 2 => __('Unfeatured', 'sabai-directory')),
        );
    }
    
    public function onFormBuildTaxonomyAdminListterms(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_categoryBundleName) {
            return;
        }
        
        $has_thumbnail = $has_marker = false;
        if (!empty($form['entities']['#options'])) {
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                if ($entity->directory_thumbnail) {                
                    $form['entities']['#options'][$entity_id]['thumbnail'] = '<img style="max-height:48px;" src="' . $this->_application->File_ThumbnailUrl($entity->directory_thumbnail[0]['name']) . '" alt="" />';
                    $has_thumbnail = true;
                }
                if ($entity->directory_map_marker) {                
                    $form['entities']['#options'][$entity_id]['marker'] = '<img style="max-height:48px;" src="' . $this->_application->File_ThumbnailUrl($entity->directory_map_marker[0]['name']) . '" alt="" />';
                    $has_marker = true;
                }
            }
        }
        if ($has_thumbnail) {
            $form['entities']['#header']['thumbnail'] = array(
                'order' => 90,
                'label' => __('Thumbnail', 'sabai-directory'),
            );
            $form['entities']['#row_attributes']['@all']['thumbnail']['style'] = 'text-align:center;';
        }
        if ($has_marker) {
            $form['entities']['#header']['marker'] = array(
                'order' => 95,
                'label' => __('Map Marker', 'sabai-directory'),
            );
            $form['entities']['#row_attributes']['@all']['marker']['style'] = 'text-align:center;';
        }
    }
    
    public function onContentAdminPostsUrlParamsFilter(&$urlParams, $context, $bundle)
    {
        if ($bundle->name === $this->_listingBundleName) {
            if ($directory_claim = $context->getRequest()->asInt('directory_claim')){
                $urlParams['directory_claim'] = $directory_claim;
            }
            if ($directory_featured = $context->getRequest()->asInt('directory_featured')){
                $urlParams['directory_featured'] = $directory_featured;
            }
        } elseif ($bundle->name === $this->_photoBundleName) {
            if ($directory_photos = $context->getRequest()->asInt('directory_photos')){
                $urlParams['directory_photos'] = $directory_photos;
            }
        }
    }
    
    public function onContentAdminPostsQuery($context, $bundle, $query, $countQuery, $sort, $order)
    {
        if ($bundle->name === $this->_listingBundleName) {
            if ($directory_claim = $context->getRequest()->asInt('directory_claim')){
                switch ($directory_claim) {
                    case 1:
                        $query->startCriteriaGroup('OR')
                            ->fieldIsGreaterThan('directory_claim', time(), 'expires_at')
                            ->fieldIs('directory_claim', 0, 'expires_at')
                            ->finishCriteriaGroup();
                        $countQuery->startCriteriaGroup('OR')
                            ->fieldIsGreaterThan('directory_claim', time(), 'expires_at')
                            ->fieldIs('directory_claim', 0, 'expires_at')
                            ->finishCriteriaGroup();
                    break;
                    case 2:
                        $query->fieldIsNull('directory_claim', 'expires_at');
                        $countQuery->fieldIsNull('directory_claim', 'expires_at');
                    break;
                    case 3:
                        $query->fieldIsOrSmallerThan('directory_claim', time(), 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                        $countQuery->fieldIsOrSmallerThan('directory_claim', time(), 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                    break;
                    case 4:
                        $query->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 7, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                        $countQuery->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 7, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                    break;
                    case 5:
                        $query->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 30, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                        $countQuery->fieldIsOrSmallerThan('directory_claim', time() + 86400 * 30, 'expires_at')
                            ->fieldIsNot('directory_claim', 0, 'expires_at');
                    break;
                }
            }
            if ($directory_featured = $context->getRequest()->asInt('directory_featured')){
                switch ($directory_featured) {
                    case 1:
                        $query->fieldIsNotNull('content_featured');
                        $countQuery->fieldIsNotNull('content_featured');
                    break;
                    case 2:
                        $query->fieldIsNull('content_featured');
                        $countQuery->fieldIsNull('content_featured');
                    break;
                }
            }
        } elseif ($bundle->name === $this->_photoBundleName) {
            if ($directory_photos = $context->getRequest()->asInt('directory_photos')){
                switch ($directory_photos) {
                    case 1:
                        $query->fieldIs('directory_photo', 1, 'official');
                        $countQuery->fieldIs('directory_photo', 1, 'official');
                    break;
                    case 2:
                        $query->fieldIsNotNull('content_reference');
                        $countQuery->fieldIsNotNull('content_reference');
                    break;
                }
            }
        }
    }
    
    public function onFormBuildContentAdminListchildposts(&$form, &$storage)
    {
        if ($form['#bundle']->name === $this->_reviewBundleName) {
            $form['entities']['#header']['photos'] = array(
                'order' => 14,
                'label' => '<i class="fa fa-camera" title="'. __('Photos', 'sabai-directory') .'">',
            );
            if (!empty($form['entities']['#options'])) {
                foreach (array(Sabai_Addon_Content::POST_STATUS_DRAFT, Sabai_Addon_Content::POST_STATUS_PENDING, Sabai_Addon_Content::POST_STATUS_PUBLISHED) as $status) {
                    $photo_counts[$status] = $this->_application->Entity_Query('content')
                        ->propertyIs('post_entity_bundle_name', $this->_photoBundleName)
                        ->propertyIs('post_status', $status)
                        ->fieldIsIn('content_reference', array_keys($form['entities']['#options']))
                        ->groupByField('content_reference')
                        ->count();
                }
                foreach ($form['entities']['#options'] as $entity_id => $data) {
                    $entity = $data['#entity'];
                    $icons = array();
                    $icons[] = $this->_application->Voting_RenderRating($entity->directory_rating['']);
                    $form['entities']['#options'][$entity_id]['title'] = '<span class="sabai-directory-icons">' . implode(PHP_EOL, $icons) . '</span> ' . $form['entities']['#options'][$entity_id]['title'];
                    $photo_count = (int)@$photo_counts[Sabai_Addon_Content::POST_STATUS_PUBLISHED][$entity_id];
                    $pending_photo_count = (int)@$photo_counts[Sabai_Addon_Content::POST_STATUS_PENDING][$entity_id] + (int)@$photo_counts[Sabai_Addon_Content::POST_STATUS_DRAFT][$entity_id];
                    $form['entities']['#options'][$entity_id] += array(
                        'photos' => $this->_application->LinkTo(
                            empty($pending_photo_count) ? $photo_count : sprintf('%d (%d)', $photo_count, $pending_photo_count),
                            $this->_application->Url('/' . strtolower($this->_name) . '/photos', array('content_reference' => $entity_id))
                        ),
                    );
                }
            }
        } elseif ($form['#bundle']->name === $this->_photoBundleName) {
            $form['entities']['#header']['thumbnail'] = array(
                'order' => 0,
                'label' => '',
            );
            $form['entities']['#header']['review'] = array(
                'order' => 12,
                'label' => __('Review', 'sabai-directory'),
            );
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                if (isset($entity->directory_photo[0]['official']) && $entity->directory_photo[0]['official'] == 1) {
                    $form['entities']['#options'][$entity_id]['title'] = '<i class="fa fa-check-circle sabai-entity-icon-directory-claimed"></i> ' . $form['entities']['#options'][$entity_id]['title'];
                }
                $form['entities']['#options'][$entity_id]['thumbnail'] = sprintf('<img src="%s" alt="" width="60" height="60" />', $this->_application->Directory_PhotoUrl($entity, 'thumbnail'));
                if ($entity->content_reference) {
                    $review = array_shift($entity->content_reference);
                    $form['entities']['#options'][$entity_id]['review'] = $this->_application->LinkTo(
                        mb_strimwidth($review->getTitle(), 0, 70, '...'),
                        $this->_application->Url($form['#bundle']->getAdminPath(), array('content_reference' => $review->getId()))
                    );
                }
            }
            $form['#filters']['directory_photos'] = array(
                'order' => 5,
                'default_option_label' => sprintf(__('All photos', 'sabai-directory')),
                'options' => array(1 => __('Official photos', 'sabai-directory'), 2 => __('Review photos', 'sabai-directory')),
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options'] += array(
                'mark_official' => __('Mark Official', 'sabai-directory'),
                'unmark_official' => __('Unmark Official', 'sabai-directory'),
            );
            $form['#submit'][0][] = array($this, 'updateEntities');
        } elseif ($form['#bundle']->name === $this->_leadBundleName) {
            $form['entities']['#header']['title'] = array(
                'label' => __('Message', 'sabai-directory'),
                'order' => 1,
            );
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $entity = $data['#entity'];
                $form['entities']['#options'][$entity_id]['title'] = Sabai::h($this->_application->Summarize($entity->getContent(), 200)) . '<div class="sabai-row-action">' . $this->_application->Menu($data['#links']) . '</div>';
            }
        }
    }
    
    public function updateEntities(Sabai_Addon_Form_Form $form)
    {
        if (empty($form->values['entities'])) return;
        
        switch ($form->values['action']) {
            case 'mark_official':
                foreach ($this->_application->Entity_Entities('content', $form->values['entities']) as $entity) {
                    if ($entity->getSingleFieldValue('directory_photo', 'official') == 1 // Already marked as official
                        || $entity->getSingleFieldValue('entity_reference') // Review photos may not become official 
                    ) {
                        continue;
                    }
                    $this->_application->Entity_Save($entity, array('directory_photo' => array('official' => 1, 'display_order' => 99)));
                }
                break;
            case 'unmark_official':
                foreach ($this->_application->Entity_Entities('content', $form->values['entities']) as $entity) {
                    if (!$entity->getSingleFieldValue('directory_photo', 'official')) { // Not marked as official
                        continue;
                    }
                    $this->_application->Entity_Save($entity, array('directory_photo' => false));
                }
                break;
        }
    }
    
    public function onVotingContentDirectoryListingEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_listingBundleName) return;
        
        $this->_application->Directory_SendListingNotification('flagged', $entity, false, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    public function onVotingContentDirectoryListingReviewEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_reviewBundleName) return;
        
        $this->_application->Directory_SendReviewNotification('flagged', $entity, false, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    public function onVotingContentDirectoryListingPhotoEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results, $vote)
    {
        if ($entity->getBundleName() !== $this->_photoBundleName) return;
        
        $this->_application->Directory_SendPhotoNotification('flagged', $entity, false, array('{flag_score_total}' => (int)$results['sum']) + $this->_application->Voting_TemplateTags($vote));
        $this->_trashPostIfSpam($entity, $results);
    }
    
    private function _trashPostIfSpam(Sabai_Addon_Entity_IEntity $entity, $results)
    {
        if ($entity->isTrashed()) return; // trashed posts can not be flagged, but just in case

        // Has the spam score reached the threshold?
        switch ($entity->getBundleName()) {
            case $this->_listingBundleName:
                $threshold = $this->_config['spam']['threshold']['listing'] + 0.3 * (int)$entity->getSingleFieldValue('voting_rating', 'sum');
                break;
            case $this->_reviewBundleName:
                $threshold = $this->_config['spam']['threshold']['review'] + 0.3 * (int)$entity->getSingleFieldValue('voting_helpful', 'sum');
                break;
            case $this->_photoBundleName:
                $threshold = $this->_config['spam']['threshold']['photo'] + 0.3 * (int)$entity->getSingleFieldValue('voting_helpful', 'sum');
                break;
            default:
                return;
        }
        if ($results['sum'] > $threshold) {
            // Move to trash and clear flags
            $this->_application->Content_TrashPosts($entity, Sabai_Addon_Content::TRASH_TYPE_SPAM, '', 0);
        }
    }
    
    public function onSabaiRunCron($lastRunTimestamp, $logs)
    {
        if (time() - $lastRunTimestamp < 86400) return; // Run this cron once a day

        $claims_deleted = array();
        // Get the number of days before expiration the notification should be sent 
        if (!$days = $this->_application->System_EmailSettings($this->_name, 'listing_expires', 'days')) {
            $days = 7;
        }
        // Fetch claims that will expire in 7 days
        $listings = $this->_application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $this->_listingBundleName)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIsOrSmallerThan('directory_claim', time() + $days * 86400, 'expires_at')
            ->fieldIsGreaterThan('directory_claim', 0, 'expires_at')
            ->fetch(); 
        foreach ($listings as $listing) {
            if (!$claim = $listing->getSingleFieldValue('directory_claim')) {
                continue;
            }
            // Notify listing owner
            $tags = array(
                '{expiration_date}' => $this->_application->Date($claim['expires_at']),
                '{expiration_date_diff}' => $this->_application->getPlatform()->getHumanTimeDiff($claim['expires_at']),
                '{listing_renew_url}' => $this->_application->Url('/' . $this->_application->getAddon('Directory')->getSlug('dashboard') . '/renew-listing', array('listing_id' => $listing->getId())),
            );
            if ($claim['expires_at'] < time()) {
                if ($claim['expires_at'] < time() - $this->_config['claims']['grace_period'] * 86400) { // more than X days after expiration?
                    $values_to_save = array('directory_claim' => false);
                    $this->_application->Action('directory_claim_expired', array($listing, &$values_to_save));
                    $this->_application->Entity_Save($listing, $values_to_save);
                    $claims_deleted[$listing->getId()] = $claim;
                } else {
                    $this->_application->Directory_SendListingNotification('expired', $listing, $this->_application->UserIdentity($claim['claimed_by']), $tags);
                }
            } else {
                $this->_application->Directory_SendListingNotification('expires', $listing, $this->_application->UserIdentity($claim['claimed_by']), $tags);
            }
        }
        if (!empty($claims_deleted)) {
            $logs[] = sprintf(__('Deleted %d expired listing claims', 'sabai-directory'), count($claims_deleted));
            if (!empty($this->_config['claims']['trash_expired'])) {
                $this->_application->Content_TrashPosts(array_keys($claims_deleted));
                $logs[] = sprintf(__('Moved %d listings with expired claims to trash', 'sabai-directory'), count($claims_deleted));
            }
            
        }

        if (!$this->_config['spam']['auto_delete']) {
            // Auto-delete spam not enabled
            return;
        }    
        // Fetch posts marked as spam and were trashed more than X days ago
        $days = $this->_config['spam']['delete_after'];
        $spam_posts = $this->_application->Entity_Query('content')
            ->propertyIsIn('post_entity_bundle_name', array($this->_listingBundleName, $this->_reviewBundleName, $this->_photoBundleName))
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
            __('Deleted %d spam posts (listings and/or reviews) from trash', 'sabai-directory'),
            count($spam_posts)
        );
    }

    public function isListingTrashable($listing, $user)
    {
        if (!empty($listing->directory_claim)) {
            return $this->_application->HasPermission($this->_listingBundleName . '_trash_own_claimed')
                && $this->_application->Directory_IsListingOwner($listing, false, $user); 
        }
        return $this->_application->HasPermission($this->_listingBundleName . '_manage')
            || ($this->_application->Entity_IsAuthor($listing, $user)
                && $this->_application->HasPermission($this->_listingBundleName . '_trash_own'));
    }

    public function isReviewTrashable($review, $user)
    {
        return $this->_application->Entity_IsAuthor($review, $user)
            && $this->_application->HasPermission($this->_reviewBundleName . '_trash_own');
    }
    
    public function onContentDirectoryListingReviewPostsPublished($bundleName, $entities)
    {
        if ($bundleName !== $this->_reviewBundleName) return;

        // Publish associated photos
        $this->_application->Content_PublishReferencingPosts(array_keys($entities));
    }
    
    public function onContentDirectoryListingReviewPostsTrashed($bundleName, $entities)
    {
        if ($bundleName !== $this->_reviewBundleName) return;
        // Trash associated photos
        $this->_application->Content_TrashReferencingPosts(array_keys($entities));
    }
        
    public function onContentDirectoryListingReviewPostsRestored($bundleName, $entities)
    {
        if ($bundleName !== $this->_reviewBundleName) return;
        // Restore associated photos
        $this->_application->Content_RestoreReferencingPosts(array_keys($entities));
    }
    
    public function onEntityBulkDeleteContentDirectoryListingReviewEntitySuccess($bundle, $entities, $extra)
    {
        if ($bundle->name !== $this->_reviewBundleName) return;
        // Delete associated photos
        $this->_application->Content_DeleteReferencingPosts(array_keys($entities));
    }
    
    public function onSabaiWebResponseRenderHtmlLayout(Sabai_Context $context, &$content)
    {
        if ($this->hasParent()) return;

        if ($this->_application->getPlatform()->isAdmin()) {
            $this->_application->LoadCss('admin.min.css', 'sabai-directory', 'sabai', 'sabai-directory');
        } else {
            $this->_application->LoadCss('main.min.css', 'sabai-directory', 'sabai', 'sabai-directory');
            if ($this->_application->getPlatform()->isLanguageRTL()) {
                $this->_application->LoadCss('main-rtl.min.css', 'sabai-directory-rtl', 'sabai-directory', 'sabai-directory');
            }
        }
    }

    public function onEntityCreateContentDirectoryListingEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        if ($entity->isPublished()) {
            $this->_application->Directory_SendListingNotification('published', $entity);
        } else {
            $this->_application->Directory_SendListingNotification(
                'submitted_admin',
                $entity,
                null,
                array('{listing_url}' => $this->_application->AdminUrl('/' . strtolower($this->_name) . '/' . $entity->getId()))
            );
        }
    }
    
    public function onEntityCreateContentDirectoryListingReviewEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_reviewBundleName) return;
        
        if ($entity->isPublished()) {
            if (isset($values['directory_rating'])) {
                // Cast vote for the parent listing
                $this->_rateListing($entity);
            }
            $this->_application->Directory_SendReviewNotification('published', $entity);
            $this->_notifyListingOwners($entity);
        } else {
            $this->_application->Directory_SendReviewNotification(
                'submitted_admin',
                $entity,
                null,
                array('{review_url}' => $this->_application->AdminUrl('/' . strtolower($this->_name) . '/reviews/' . $entity->getId()))
            );
        }
    }
    
    public function onEntityUpdateContentDirectoryListingReviewEntitySuccess($bundle, $entity, $oldEntity, $values)
    {
        if ($bundle->name !== $this->_reviewBundleName) return;
        
        if ($entity->isPublished()) {
            if (isset($values['directory_rating']) // rating changed
                || isset($values['content_post_status']) // review was just published
            ) {
                $this->_rateListing($entity, true);
            }
        } else {
            if ($oldEntity->isPublished()) {
                $this->_rateListing($entity, false, true);
            }
        }
    }
    
    protected function _rateListing($review, $isEdit = false, $isDelete = false)
    {
        if (!$listing = $this->_application->Content_ParentPost($review, false)) return;
        
        $this->_application->Voting_CastVote(
            $listing,
            'rating',
            $review->getFieldValue('directory_rating'),
            array('reference_id' => $review->getId(), 'user_id' => $review->getAuthorId(), 'edit' => $isEdit, 'delete' => $isDelete, 'auto_calculated' => true)
        );
    }

    public function onEntityCreateContentDirectoryListingPhotoEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_photoBundleName) return;
        
        if ($entity->content_reference) return; // do not notify if 
        
        if ($entity->isPublished()) {
            $this->_application->Directory_SendPhotoNotification('published', $entity);
            $this->_notifyListingOwners($entity);
        } else {
            $this->_application->Directory_SendPhotoNotification(
                'submitted_admin',
                $entity,
                null,
                array('{photo_url}' => $this->_application->AdminUrl('/' . strtolower($this->_name) . '/photos/' . $entity->getId()))
            );
        }
    }

    public function onEntityCreateContentDirectoryListingLeadEntitySuccess($bundle, $entity, $values)
    {
        if ($bundle->name !== $this->_leadBundleName) return;
        
        if ($entity->isPublished()) {
            $this->_notifyListingOwners($entity);
        } else {
            $this->_application->Directory_SendLeadNotification(
                'submitted_admin',
                $entity,
                null,
                array('{lead_url}' => $this->_application->AdminUrl('/' . strtolower($this->_name) . '/leads/' . $entity->getId()))
            );
        }
    }
    
    public function onEntityCreateTaxonomyDirectoryCategoryEntitySuccess($bundle, $entity, $values)
    {
        $this->_maybeDeleteCategoryMapMarkerURLsCache($bundle, $values);
    }
    
    public function onEntityUpdateTaxonomyDirectoryCategoryEntitySuccess($bundle, $entity, $oldEntity, $values)
    {
        $this->_maybeDeleteCategoryMapMarkerURLsCache($bundle, $values);
    }
    
    private function _maybeDeleteCategoryMapMarkerURLsCache($bundle, $values)
    {
        if ($bundle->name !== $this->_categoryBundleName
            || !isset($values['directory_map_marker'])
        ) return;

        // Delete cached category custom map marker URLs 
        $this->_application->getPlatform()->deleteCache($this->_categoryBundleName . '_map_marker_urls');
    }
    
    public function onContentPostPublished($entity)
    {
        if ($entity->getBundleName() === $this->_listingBundleName) {
            $this->_application->Directory_SendListingNotification(array('published', 'approved'), $entity, null, array('{listing_claim_url}' => $this->_application->Entity_Url($entity, '/' . $this->getSlug('claim'))));
        } elseif ($entity->getBundleName() === $this->_reviewBundleName) {
            $this->_application->Directory_SendReviewNotification(array('published', 'approved'), $entity);
            $this->_notifyListingOwners($entity);
        } elseif ($entity->getBundleName() === $this->_photoBundleName) {
            $this->_application->Directory_SendPhotoNotification(array('published', 'approved'), $entity);
            $this->_notifyListingOwners($entity);
        } elseif ($entity->getBundleName() === $this->_leadBundleName) {
            $this->_notifyListingOwners($entity);
        }
    }

    public function onCommentSubmitCommentSuccess($comment, $isEdit, $entity)
    {
        if ($isEdit
            || $entity->getAuthorId() === $comment->user_id
        ) {
            return;
        }
        switch ($entity->getBundleName()) {
            case $this->_reviewBundleName:
                $this->_application->Directory_SendReviewNotification('commented', $entity, null, $this->_application->Comment_TemplateTags($comment));
                break;
            case $this->_photoBundleName:
                $this->_application->Directory_SendPhotoNotification('commented', $entity, null, $this->_application->Comment_TemplateTags($comment));
                break;
        }
    }
    
    private function _notifyListingOwners($entity)
    {
        // Notify listing owners
        if (!$listing = $this->_application->Content_ParentPost($entity)) return;
        
        $claimed_by = $listing->getSingleFieldValue('directory_claim', 'claimed_by');
        if ($entity->getBundleName() === $this->_leadBundleName) {
            if ($claimed_by
                && ($owner = $this->_application->UserIdentity($claimed_by))
            ) {
                $recipients = empty($listing->directory_contact[0]['email']) ? array() : array('contact' => array('name' => $listing->getTitle(), 'email' => $listing->directory_contact[0]['email']));
                $recipients['owner'] = $owner;
            } else {
                $config = $this->_config['claims']['unclaimed']['leads'];
                if (empty($config['enable'])) return;
            
                $recipients = array();
                if (!empty($config['to_author'])) {
                    $recipients['author'] = $listing->getAuthor();
                }
                if (!empty($config['to_contact'])
                    && !empty($listing->directory_contact[0]['email'])
                ) {
                    $recipients['contact'] = array('name' => $listing->getTitle(), 'email' => $listing->directory_contact[0]['email']);
                }
            }
            $recipients = $this->_application->Filter('directory_lead_added_notification_recipients', $recipients, array($listing, $owner));
            if (!empty($recipients)) {
                $this->_application->Directory_SendLeadNotification('added', $entity, $recipients, $this->_application->Entity_Author($entity));
            }
        } else {
            if ($claimed_by
                && ($owner = $this->_application->UserIdentity($claimed_by))
            ) {
                if ($entity->getBundleName() === $this->_reviewBundleName) {
                    $this->_application->Directory_SendReviewNotification('added', $entity, $owner);
                } elseif ($entity->getBundleName() === $this->_photoBundleName) {
                    $this->_application->Directory_SendPhotoNotification('added', $entity, $owner);
                }
            }
        }
    }
    
    public function onSystemUserActivityFilter(&$activity, $identity, $counts)
    {
        if ($this->hasParent()) return;
        
        // Count all claimed listings
        $query = $this->_application->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIs('post_entity_bundle_type', 'directory_listing');
        if ($this->_config['display']['prof_claimed_only']) {
            $query->startCriteriaGroup()
                ->fieldIs('directory_claim', $identity->id, 'claimed_by')
                ->fieldIsOrGreaterThan('directory_claim', time(), 'expires_at')
            ->finishCriteriaGroup();
        } else {
            $query->startCriteriaGroup('OR')
                ->startCriteriaGroup()
                    ->propertyIs('post_user_id', $identity->id)
                    ->fieldIsNull('directory_claim', 'claimed_by')
                ->finishCriteriaGroup()
                ->startCriteriaGroup()
                    ->fieldIs('directory_claim', $identity->id, 'claimed_by')
                    ->fieldIsOrGreaterThan('directory_claim', time(), 'expires_at')
                ->finishCriteriaGroup()
            ->finishCriteriaGroup();
        }
        $listing_counts = $query->groupByProperty('post_entity_bundle_name')->count();
        // Count all non-official photos
        $photo_counts = $this->_application->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIs('post_user_id', $identity->id)
            ->propertyIs('post_entity_bundle_type', 'directory_listing_photo')
            ->fieldIsNull('directory_photo', 'official')
            ->groupByProperty('post_entity_bundle_name')
            ->count();
        // Activity for this add-on
        $_activity = array();
        if (isset($listing_counts[$this->_listingBundleName]) || isset($counts[$this->_reviewBundleName]) || isset($counts[$this->_photoBundleName])) {
            $_activity[$this->_name] = array(               
                'stats' => array(
                    $this->_listingBundleName => array(
                        'url' => '/' . $this->getSlug('directory') . '/users/' . $identity->username,
                        'format' => _n('%s listing', '%s listings', $count = isset($listing_counts[$this->_listingBundleName]) ? $listing_counts[$this->_listingBundleName] : 0, 'sabai-directory'),
                        'count' => $count,
                        'type' => 'directory_listing',
                    ),
                    $this->_reviewBundleName => array(
                        'url' => '/' . $this->getSlug('directory') . '/users/' . $identity->username . '/reviews',
                        'format' => _n('%s review', '%s reviews', $count = isset($counts[$this->_reviewBundleName]) ? $counts[$this->_reviewBundleName] : 0, 'sabai-directory'),
                        'count' => $count,
                        'type' => 'directory_listing_review',
                    ),
                    $this->_photoBundleName => array(
                        'url' => '/' . $this->getSlug('directory') . '/users/' . $identity->username . '/photos',
                        'format' => _n('%s photo', '%s photos', $count = isset($photo_counts[$this->_photoBundleName]) ? $photo_counts[$this->_photoBundleName] : 0, 'sabai-directory'),
                        'count' => $count,
                        'type' => 'directory_listing_photo',
                    ),
                ),
                'title' => $this->getTitle('directory'),
            );
        }
        // Activity for cloned add-ons
        foreach ($this->_application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch() as $addon) {
            $directory_addon = $this->_application->getAddon($addon->name);
            $listing_bundle_name = $directory_addon->getListingBundleName();
            $review_bundle_name = $directory_addon->getReviewBundleName();
            $photo_bundle_name = $directory_addon->getPhotoBundleName();
            if (isset($listing_counts[$listing_bundle_name]) || isset($counts[$review_bundle_name]) || isset($photo_counts[$photo_bundle_name])) {
                $_activity[$addon->name] = array(               
                    'stats' => array(
                        $listing_bundle_name => array(
                            'url' => '/' . $directory_addon->getSlug('directory') . '/users/' . $identity->username,
                            'format' => _n('%s listing', '%s listings', $count = isset($listing_counts[$listing_bundle_name]) ? $listing_counts[$listing_bundle_name] : 0, 'sabai-directory'),
                            'count' => $count,
                            'type' => 'directory_listing',
                        ),
                        $review_bundle_name => array(
                            'url' => '/' . $directory_addon->getSlug('directory') . '/users/' . $identity->username . '/reviews',
                            'format' => _n('%s review', '%s reviews', $count = isset($counts[$review_bundle_name]) ? $counts[$review_bundle_name] : 0, 'sabai-directory'),
                            'count' => $count,
                            'type' => 'directory_listing_review',
                        ),
                        $photo_bundle_name => array(
                            'url' => '/' . $directory_addon->getSlug('directory') . '/users/' . $identity->username . '/photos',
                            'format' => _n('%s photo', '%s photos', $count = isset($photo_counts[$photo_bundle_name]) ? $photo_counts[$photo_bundle_name] : 0, 'sabai-directory'),
                            'count' => $count,
                            'type' => 'directory_listing_photo',
                        ),
                    ),
                    'title' => $directory_addon->getTitle('directory'),
                );
            }
        }
        if (!empty($_activity)) {
            $activity += $this->_application->Filter('directory_user_profile_activity', $_activity, array($identity));
        }
    }
        
    public function onDirectoryInstallSuccess($addon)
    {
        if ($addon->getName() !== $this->_name) return;
        
        //$this->_application->Directory_CreateSampleData($addon->getName());
    }
    
    public function onDirectoryListingClaimStatusChange($claim)
    {
        if ($claim->entity_bundle_name !== $this->_listingBundleName) {
            return;
        }
        $this->_application->Directory_SendClaimNotification($claim->status, $claim);
    }
    
    public function onSystemRoutesFilter(&$routes, $rootPath, $entityName)
    {
        if ($entityName !== 'Route'
            || strpos('/' . $this->getSlug('directory') . '/', $rootPath . '/') !== 0
        ) return;
        
        $path = '/'. $this->getSlug('listing') . '/:slug/';
        $default = array(
            'addon' => $this->_name,
            'controller_addon' => 'Directory',
            'callback_addon' => $this->_name,
            'callback_path' => 'listing_tab',
            'access_callback' => true,
            'type' => Sabai::ROUTE_INLINE_TAB,
            'controller' => 'ListingTab',
            'ajax' => false,
            'class' => '',
            'method' => '',
            'data' => array(),
        );
        $default = $this->_application->Filter('directory_custom_tab_route_default_settings', $default, array($this->_name));
        foreach ($this->_config['display']['listing_tabs']['options'] as $tab_name => $tab_label) {
            $tab_name = strtolower($tab_name);
            $tab_path = $path . $tab_name;
            if (!in_array($tab_name, $this->_config['display']['listing_tabs']['default'])) {
                $routes[$tab_path]['type'] = Sabai::ROUTE_NORMAL;
            } else {
                if (!isset($routes[$tab_path])) {
                    // custom tab
                    $routes[$tab_path] = array(
                        'path' => $tab_path,
                        'title' => $tab_label,
                        'data' => array('hide_empty' => true)
                    ) + $default;
                    $routes[rtrim($path, '/')]['routes'][$tab_name] = $tab_path;
                } else {
                    $routes[$tab_path]['title'] = $tab_label;
                }
                $routes[$tab_path]['weight'] = array_search($tab_name, $this->_config['display']['listing_tabs']['default']);
            }
        }
    }
    
    public function onSystemEmailSettingsFilter(&$settings, $addonName)
    {
        if ($this->_application->getAddon($addonName)->getType() !== 'Directory') return;
        
        $settings += $this->_application->Directory_NotificationSettings($addonName);
    }
    
    public function onEntityLoadFieldValuesFilter(&$fieldValues, $entity, $bundle, $fields, $cache)
    {        
        if (!$cache || $bundle->name !== $this->_listingBundleName) return;
        
        if (empty($fieldValues['directory_claim'])
            && ($fields_allowed = (array)@$this->_config['claims']['unclaimed']['fields'])
        ) {        
            $fieldValues = $this->_application->Directory_FilterFieldValues($fieldValues, $fields_allowed);
        }
        
        $category_ids = array();
        if (!empty($fieldValues['directory_category'])) {
            foreach ($fieldValues['directory_category'] as $category) {
                $category_ids[$category->getId()] = $category->getId();
                if (!$category->getParentId()) continue;
                
                foreach ($this->_application->Taxonomy_Parents($category, false) as $parent) {
                    $category_ids[$parent->id] = $parent->id;
                }
            }
        }
        foreach ($fields as $field_name => $field) {
            if (!$categories = $field->getFieldData('directory_category')) continue;
            
            if (!empty($category_ids)) {
                foreach ($categories as $category_id) {
                    if (in_array($category_id, $category_ids)) {
                        continue 2; // the field is allowed for one of the selected categories
                    }
                }
            }
            unset($fieldValues[$field->getFieldName()]); // no valid categories selected for this field
        }
    }
    
    public function onGoogleMapsInstallSuccess(Sabai_Addon $addon, ArrayObject $log)
    {
        foreach ($this->_application->getModel('Bundle', 'Entity')->type_is('directory_listing')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $this->_application->getAddon('Entity')->createEntityField(
                $bundle,
                'directory_location', 
                array(
                    'type' => 'googlemaps_marker',
                    'settings' => array(),
                    'max_num_items' => 1,
                    'weight' => 7,
                    'label' => __('Location', 'sabai-directory'),
                    'required' => true,
                    'renderer_settings' => array(
                        'map' => array(
                            'googlemaps_marker' => array(),
                        ),
                    ),
                ),
                Sabai_Addon_Entity::FIELD_REALM_BUNDLE_DEFAULT
            );
        }
    }
    
    public function onFieldUIFieldViewsFilter(&$views, $fieldType, Sabai_Addon_Entity_Model_Bundle $bundle, $isCustomField)
    {
        if ($bundle->addon !== $this->_name) return;
        
        switch ($bundle->type) {
            case 'directory_listing':
                $views += array(
                    'default' => __('Detailed view', 'sabai-directory'),
                    'summary' => array('title' => __('Summary view', 'sabai-directory'), 'inherit' => true, 'display' => false),
                    'map' => array('title' => __('Map view', 'sabai-directory'), 'inherit' => true, 'display' => false),
                    'grid' => array('title' => __('Grid view', 'sabai-directory'), 'inherit' => true, 'display' => false),
                );
                $default_tabs = $this->getListingDefaultTabs();
                foreach ((array)@$this->_config['display']['listing_tabs']['options'] as $tab_name => $tab_label) {
                    if (isset($default_tabs[$tab_name])) continue;
                    $views['tab_' . $tab_name] = array('title' => sprintf(__('Tab view - %s', 'sabai-directory'), Sabai::h($tab_label)), 'inherit' => true, 'display' => false);
                }
                if ($fieldType === 'content_post_title') {
                    unset($views['default']);
                }
                break;
            case 'directory_listing_review':
                $views += array(
                    'default' => __('Detailed view', 'sabai-directory'),
                    'summary' => array('title' => __('Summary view', 'sabai-directory'), 'inherit' => true, 'display' => false),
                );
                break;
            case 'directory_listing_lead':
                $views += array(
                    'default' => __('Detailed view', 'sabai-directory'),
                );
                break;
            case 'directory_category':
                if (!in_array($fieldType, array('directory_map_marker', 'directory_thumbnail'))) {               
                    $views += array(
                        'default' => __('Detailed view', 'sabai-directory'),
                    );
                }
                break;
        }
    }
    
    public function onFieldUIFieldViewRenderersFilter(&$renderers, $fieldType, Sabai_Addon_Entity_Model_Bundle $bundle, $view, $isCustomField)
    {
        if ($bundle->addon !== $this->_name) return;
        
        if ($fieldType === 'directory_photos') {
            if (in_array($view, array('map', 'grid'))) {
                unset($renderers['directory_carousel']);
            }
        } elseif ($fieldType === 'googlemaps_marker') {
            if ($view !== 'default') {
                unset($renderers['googlemaps_map']);
            }
        }
    }
    
    public function onFormBuildFielduiAdminCreateField(&$form, &$storage)
    {
        if ($form['#bundle'] !== $this->_listingBundleName || !isset($storage['field_widget'])) return;
        
        $this->_onFormBuildFielduiAdminField($form, $storage);
    }
    
    public function onFormBuildFielduiAdminEditField(&$form, &$storage)
    {
        if ($form['#bundle'] !== $this->_listingBundleName) return;
        
        $add_directory_unclaimed = false;
        $directory_unclaimed_default = null;
        // Check if the field can be enabled/disabled and not disabled by default
        $field_options = $this->_application->Directory_FieldOptions($form['#bundle'], true);
        $field_name = $form['#field']->getFieldName();
        if (isset($field_options[0][$field_name]) && !in_array($field_name, $field_options[1])) {
            $add_directory_unclaimed = true;
            $directory_unclaimed_default = !isset($this->_config['claims']['unclaimed']['fields']) // can be null if upgrading or freshly installed
                || in_array($field_name, $this->_config['claims']['unclaimed']['fields']);
        }
        $this->_onFormBuildFielduiAdminField($form, $storage, $form['#field']->isCustomField(), $form['#field']->getFieldData('directory_category'), $add_directory_unclaimed, $directory_unclaimed_default);
    }
    
    public function _onFormBuildFielduiAdminField(&$form, &$storage, $addCategorySelect = true, array $directoryCategoryDefault = null, $addDirectoryUnclaimed = true, $directoryUnclaimedDefault = true)
    {
        if ($addDirectoryUnclaimed) {
            $form['basic']['directory_unclaimed'] = array(
                '#type' => 'checkbox',
                '#default_value' => $directoryUnclaimedDefault,
                '#title' => __('Enable this field for unclaimed listings', 'sabai-directory'),
                '#weight' => 100,
            );
        }
        
        if ($addCategorySelect) {
            $depth = $this->_application->getModel(null, 'Taxonomy')->getGateway('Term')->getMaxDepth($this->_categoryBundleName);
            $form['basic']['directory_category'] = array(
                '#title' => __('Categories', 'sabai-directory'),
                '#description' => __('Select categories to which the field belongs. The field will then be enabled only when one or more of the following categories are selected.', 'sabai-directory'),
                '#weight' => 99,
                '#collapsible' => false,
                '#tree' => true,
                '#class' => 'sabai-form-group',
                '#element_validate' => array(array($this, 'validateTaxonomySelect')),
            );
            $next_index = 1;
            if (!empty($directoryCategoryDefault)) {
                foreach ($directoryCategoryDefault as $category_id) {
                    $form['basic']['directory_category'][] = $this->_getSelectCategoryField($depth, $category_id);
                    ++$next_index;
                }
            }
            $form['basic']['directory_category'][] = $this->_getSelectCategoryField($depth);
            $form['basic']['directory_category']['_add'] = array(
                '#type' => 'item',
                '#markup' => sprintf(
                    '<a href="#" class="sabai-btn sabai-btn-default sabai-btn-xs sabai-form-field-add" data-field-name="directory_category" data-field-next-index="%d"><i class="fa fa-plus"></i> %s</a>',
                    $next_index,
                    __('Add More', 'sabai-directory')
                ),
                '#class' => 'sabai-form-field-add',
            );
        }
        
        if ($addDirectoryUnclaimed) {
            $form['#submit'][Sabai_Addon_Form::FORM_CALLBACK_WEIGHT_DEFAULT - 1][] = array($this, 'submitFielduiAdminField');
        }
    }
    
    protected function _getSelectCategoryField($depth, $value = null)
    {
        $default_text = sprintf(__('Select %s', 'sabai-directory'), $this->_application->Entity_BundleLabel($this->_categoryBundleName, true));
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
            $url = $this->_application->MainUrl('/sabai/taxonomy/child_terms', array('bundle' => $this->_categoryBundleName, Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');
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
        $terms = $this->_application->Taxonomy_Terms($this->_categoryBundleName);
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
    
    public function submitFielduiAdminField($form)
    {
        if (!isset($form->values['directory_unclaimed'])) return;
        
        if (isset($form->settings['#field'])) {
            $field_name = $form->settings['#field']->getFieldName();
        } else {
            // new field
            $field_name = isset($form->settings['#field_name']) ? $form->settings['#field_name'] : 'field_' . $form->values['name'];
        }
        if (!isset($this->_config['claims']['unclaimed']['fields'])) {
            // Enable all fields since the setting has not been initialized
            $field_options = $this->_application->Directory_FieldOptions($this->_listingBundleName, true);
            // Exclude default fields
            foreach ($field_options[1] as $default_field) {
                unset($field_options[0][$default_field]);
            }
            $this->_config['claims']['unclaimed']['fields'] = array_keys($field_options[0]);
        }
        $save = false;
        if (!empty($form->values['directory_unclaimed'])) { 
            if (!in_array($field_name, $this->_config['claims']['unclaimed']['fields'])) {
                $this->_config['claims']['unclaimed']['fields'][] = $field_name;
                $save = true;
            }
        } else {
            if (in_array($field_name, $this->_config['claims']['unclaimed']['fields'])) {
                $key = array_search($field_name, $this->_config['claims']['unclaimed']['fields']);
                unset($this->_config['claims']['unclaimed']['fields'][$key]);
                $save = true;
            }
        }
        if ($save) $this->saveConfig($this->_config, false);
        unset($form->values['directory_unclaimed']);
    }
    
    public function onFieldUIFieldDataFilter(&$fieldData, $bundle, $fieldName, $values)
    {
        $fieldData['data']['directory_category'] = array();
        
        if (!empty($values['directory_category'])) {
            foreach (array_unique(array_filter($values['directory_category'])) as $category_id) {
                $fieldData['data']['directory_category'][] = $category_id;
            }
        }
    }
    
    public function onFormBuildEntityForm(&$form, &$storage)
    {
        if ($form['#bundle']->name !== $this->_listingBundleName) return;
        
        foreach ($form['#fields'] as $field_name => $field) {
            if (!$category_ids = $field->getFieldData('directory_category')) {
                continue;
            }
            if (!isset($form['directory_category'])) {
                // category field is disabled
                unset($form[$field_name]);
                continue;
            }
            $form[$field_name]['#states']['visible']['.sabai-entity-field-name-directory-category select'] = array(
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
        foreach ($form->values['directory_category'] as $cateogry_id) {
            if (in_array($cateogry_id, $categoryIds)) return true;
        }
        return false;
    }
    
    public function onEntityFilterFormFiltersFilter(&$filters, $bundle)
    {
        if ($bundle->name !== $this->_listingBundleName) return;
        
        if (!empty($_REQUEST['category'])) {
            $category_id = $_REQUEST['category'];
        } elseif (isset($GLOBALS['sabai_entity']) && $GLOBALS['sabai_entity']->getBundleName() === $this->_categoryBundleName) {
            $category_id = $GLOBALS['sabai_entity']->getId();
        }

        foreach ($filters as $filter_name => $filter) {
            if (($category_ids = $filter['#filter']->Field->getFieldData('directory_category'))
                && (!isset($category_id) || !in_array($category_id, $category_ids))
            ) {
                // Hide the field but let the form element process so that required js scripts are loaded on page load
                $filters[$filter_name]['#template'] = '';
            }
        }
    }
}
