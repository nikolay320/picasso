<?php
class Sabai_Addon_Directory_ContentType implements Sabai_Addon_Content_IContentType
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function contentTypeGetInfo()
    {
        if ($this->_name === $this->_addon->getListingBundleName()) {
            return array(
                'type' => 'directory_listing',
                'path' => '/' . $this->_addon->getSlug('directory'),
                'admin_path' => '/' . strtolower($this->_addon->getName()),
                'label' => $this->_addon->getApplication()->_t(_n_noop('Listings', 'Listings', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Listing', 'Listing', 'sabai-directory'), 'sabai-directory'),
                'permalink_path' => '/' . $this->_addon->getSlug('listing'),
                'properties' => array(
                    'post_title' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 6,
                        'view' => array(
                            'map' => 'default',
                        ),
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 25,
                    ),
                ),
                'fields' => array(
                    'directory_header_user' => array(
                        'type' => 'sectionbreak',
                        'weight' => -1,
                        'label' => __('Your Info', 'sabai-directory'),
                        'data' => array('user_roles' => array('_guest_')),
                    ),
                    'directory_header_essential' => array(
                        'type' => 'sectionbreak',
                        'weight' => 5,
                        'label' => __('Essential Info', 'sabai-directory'),
                    ),
                    'directory_header_contact' => array(
                        'type' => 'sectionbreak',
                        'weight' => 10,
                        'label' => __('Contact Info', 'sabai-directory'),
                    ),
                    'directory_header_social' => array(
                        'type' => 'sectionbreak',
                        'weight' => 15,
                        'label' => __('Social Accounts', 'sabai-directory'),
                    ),
                    'directory_header_additional' => array(
                        'type' => 'sectionbreak',
                        'weight' => 20,
                        'label' => __('Additional Info', 'sabai-directory'),
                    ),
                    'directory_location' => array(
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
                    'directory_contact' => array(
                        'type' => 'directory_contact',
                        'settings' => array(),
                        'max_num_items' => 1,
                        'weight' => 12,
                        'label' => __('Contact Info', 'sabai-directory'),
                        'hide_label' => true,
                        'renderer_settings' => array(
                            'map' => array(
                                'directory_contact' => array(
                                    'hide' => array('mobile', 'fax'),
                                ),
                            ),
                        ),
                    ),
                    'directory_social' => array(
                        'type' => 'directory_social',
                        'settings' => array(),
                        'max_num_items' => 1,
                        'weight' => 18,
                        'label' => __('Social Accounts', 'sabai-directory'),
                        'hide_label' => true,
                        'view' => array(
                            'summary' => false,
                            'map' => false,
                        ),
                    ),
                    'directory_claim' => array(
                        'type' => 'directory_claim',
                        'label' => __('Owner', 'sabai-directory'),
                        'max_num_items' => 0,
                        'filter' => array(
                            'type' => 'directory_claim',
                            'name' => 'directory_claim',
                            'title' => __('Claimed/Unclaimed', 'sabai-directory'),
                            'col' => 2,
                            'weight' => 2,
                        ),
                    ),
                    'directory_photos' => array(
                        'type' => 'directory_photos',
                        'label' => __('Photos', 'sabai-directory'),
                        'weight' => 25,
                        'renderer' => 'directory_photos',
                        'renderer_settings' => array(
                            'summary' => array(
                                'directory_photos' => array(
                                    'feature_size' => 'thumbnail',
                                    'thumbnail' => false,
                                    'link' => 'page',
                                ),
                                'directory_carousel' => array(
                                    'size' => 'thumbnail',
                                ),
                            ),
                            'map' => array(
                                'directory_photos' => array(
                                    'feature_size' => 'thumbnail',
                                    'thumbnail' => false,
                                    'link' => 'page',
                                ),
                            ),
                            'grid' => array(
                                'directory_photos' => array(
                                    'feature_size' => 'thumbnail',
                                    'thumbnail' => false,
                                    'link' => 'page',
                                ),
                            ),
                        ),
                    ),
                ),
                'taxonomy_terms' => array(
                    $this->_addon->getCategoryBundleName() => array(
                        'required' => false,
                        'max_num_items' => 0,
                        'weight' => 9,
                        'filter' => false,
                    ),
                ),
                'voting_flag' => true,
                'voting_rating' => true,
                'content_body' => array(
                    'required' => false,
                    'label' => __('Listing Description', 'sabai-directory'),
                    'widget_settings' => array('rows' => 10),
                    'weight' => 23,
                    'filter' => false,
                    'renderer_settings' => array(
                        'summary' => array(
                            'text' => array(
                                'trim' => array('enable' => true, 'length' => 100, 'marker' => '...'),
                            ),
                        ),
                    ),
                ),
                'content_featurable' => array(
                    'filter' => array('col' => 2, 'weight' => 1),
                ),
                'content_guest_author' => array(
                    'weight' => 0,
                    'hide_label' => true,
                ),
                'content_permissions' => array(
                    'edit_own' => array('label' => __('Edit own unclaimed %1$s', 'sabai-directory'), 'default' => true),
                    'edit_any' => array('label' => __('Edit any unclaimed %2$s', 'sabai-directory')),
                    'trash_own' => array('label' => __('Delete own unclaimed %1$s', 'sabai-directory')),
                    'trash_own_claimed' => array('label' => __('Delete own claimed %1$s', 'sabai-directory')),
                    'manage' => array('label' => __('Delete any unclaimed %2$s', 'sabai-directory')),
                    'claim' => array('label' => __('Claim existing %1$s', 'sabai-directory'), 'default' => true),
                    'voting_rating' => false,
                    'voting_own_rating' => false,
                ),
                'file_content_icons' => false,
                'filterable' => true,
                'social_shareable' => true,
                'author_helper' => 'Directory_ListingOwner',
            );
        } elseif ($this->_name === $this->_addon->getReviewBundleName()) {
            return array(
                'type' => 'directory_listing_review',
                'path' => '/' . $this->_addon->getSlug('directory') . '/reviews',
                'admin_path' => '/' . strtolower($this->_addon->getName()) . '/reviews',
                'parent' => $this->_addon->getListingBundleName(),
                'label' => $this->_addon->getApplication()->_t(_n_noop('Reviews', 'Reviews', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Review', 'Review', 'sabai-directory'), 'sabai-directory'),
                'properties' => array(
                    'post_title' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 5,
                        'renderer_settings' => array(
                            'default' => array(
                                'content_post_title' => array(
                                    'link' => false,
                                ),
                            ),
                        ),
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'weight' => 25,
                    ),
                ),
                'fields' => array(
                    'directory_rating' => array(
                        'type' => 'directory_rating',
                        'max_num_items' => 1,
                        'weight' => 3,
                        'label' => __('Rating', 'sabai-directory'),
                        'required' => true,
                        'filter' => array(
                            'type' => 'directory_rating',
                            'name' => 'directory_rating',
                            'title' => __('Rating', 'sabai-directory'),
                            'col' => 1,
                        ),
                    ),
                    'directory_photos' => array(
                        'type' => 'directory_photos',
                        'label' => __('Photos', 'sabai-directory'),
                        'weight' => 15,
                        'renderer' => 'directory_photos',
                        'renderer_settings' => array(
                            'default' => array(
                                'directory_photos' => array(
                                    'feature' => false,
                                    'cols' => 6,
                                    'hidden_xs' => false,
                                ),
                            ),
                        ),
                    ),
                ),
                'comment_comments' => array(),
                'voting_flag' => true,
                'voting_helpful' => array('title' => __('Votes', 'sabai-directory')),
                'content_body' => array(
                    'required' => true,
                    'label' => __('Review', 'sabai-directory'),
                    'widget_settings' => array('rows' => 10),
                    'weight' => 10,
                    'filter' => array('col' => 2),
                    'renderer_settings' => array(
                        'summary' => array(
                            'text' => array(
                                'trim' => array('enable' => true, 'length' => 100, 'marker' => '...'),
                            ),
                        ),
                    ),
                ),
                'content_guest_author' => array(
                    'weight' => 1,
                    'hide_label' => true,
                ),
                'content_activity' => array('directory_rating'),
                'file_content_icons' => false,
                'content_permissions' => array(
                    'manage' => array('label' => __('Delete any %2$s', 'sabai-directory')),
                ),
                'filterable' => true,
            );
        } elseif ($this->_name === $this->_addon->getPhotoBundleName()) {
            return array(
                'type' => 'directory_listing_photo',
                'path' => '/' . $this->_addon->getSlug('directory') . '/photos',
                'admin_path' => '/' . strtolower($this->_addon->getName()) . '/photos',
                'parent' => $this->_addon->getListingBundleName(),
                'label' => $this->_addon->getApplication()->_t(_n_noop('Photos', 'Photos', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Photo', 'Photo', 'sabai-directory'), 'sabai-directory'),
                'properties' => array(
                    'post_title' => array(
                        'widget' => 'content_post_title_hidden', // disable title property field
                        'weight' => 2,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 25,
                    ),
                ),
                'fields' => array(
                    'directory_photo' => array(
                        'type' => 'directory_photo',
                        'widget' => false, // no widget
                        'max_num_items' => 1,
                    ),
                ),
                'voting_helpful' => array('button_enable' => true, 'label' => __('Votes', 'sabai-directory')),
                'voting_flag' => true,
                'content_title' => false,
                'content_body' => false,
                'content_guest_author' => array(
                    'weight' => 1,
                    'hide_label' => true,
                ),
                'content_reference' => $this->_addon->getReviewBundleName(),
                'fieldui_enable' => false,
                'file_image' => array(
                    'label' => __('Photo', 'sabai-directory'),
                    'settings' => array(),
                    'max_num_items' => 1,
                    'weight' => 13,
                    'widget' => 'file_upload'
                ),
                'file_content_icons' => false,
                'content_permissions' => array(
                    // guests are allowed to upload photos with listings/reviews only, since there is no guest field
                    'add' => array('guest_allowed' => false),
                    'add2' => array('guest_allowed' => false),
                    'edit_own' => false,
                    'edit_any' => false,
                    'trash_own' => false,
                    'manage' => array('label' => __('Delete any non-official %2$s', 'sabai-directory')),
                ),
                'comment_comments' => array(),
            );
        } elseif ($this->_name === $this->_addon->getLeadBundleName()) {
            return array(
                'type' => 'directory_listing_lead',
                'path' => '/' . $this->_addon->getSlug('directory') . '/leads',
                'admin_path' => '/' . strtolower($this->_addon->getName()) . '/leads',
                'parent' => $this->_addon->getListingBundleName(),
                'label' => $this->_addon->getApplication()->_t(_n_noop('Leads', 'Leads', 'sabai-directory'), 'sabai-directory'),
                'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Lead', 'Lead', 'sabai-directory'), 'sabai-directory'),
                'public' => false,
                'properties' => array(
                    'post_title' => array(
                        'widget' => 'content_post_title_hidden', // disable title property field
                        'weight' => 2,
                    ),
                    'post_published' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 23,
                    ),
                    'post_user_id' => array(
                        'required' => true, // overrwrite the core setting
                        'weight' => 25,
                    ),
                ),
                'fields' => array(),
                'content_body' => array(
                    'required' => true,
                    'label' => __('Message', 'sabai-directory'),
                    'widget_settings' => array('rows' => 10, 'hide_buttons' => true, 'hide_preview' => true),
                    'weight' => 10,
                ),
                'content_guest_author' => array(
                    'weight' => 1,
                    'hide_label' => true,
                ),
                'content_permissions' => array(
                    'edit_own' => false,
                    'edit_any' => false,
                    'trash_own' => false,
                    'manage' => false,
                ),
            );
        }
    }
    
    public function contentTypeIsPostTrashable(Sabai_Addon_Content_Entity $entity, SabaiFramework_User $user)
    {
        if ($this->_name === $this->_addon->getListingBundleName()) {
            return $this->_addon->isListingTrashable($entity, $user);
        } elseif ($this->_name === $this->_addon->getReviewBundleName()) {
            return $this->_addon->isReviewTrashable($entity, $user);
        } elseif ($this->_name === $this->_addon->getPhotoBundleName()) {
            return false;
        } elseif ($this->_name === $this->_addon->getLeadBundleName()) {
            return false;
        }
    }
    
    public function contentTypeIsPostRoutable(Sabai_Addon_Content_Entity $entity, SabaiFramework_User $user)
    {
        if ($this->_name === $this->_addon->getLeadBundleName()) {
            return ($listing = $this->_addon->getApplication()->Content_ParentPost($entity))
                && $this->_addon->getApplication()->Directory_IsListingOwner($listing, true, $user);
        }
        return true;
    }
}
