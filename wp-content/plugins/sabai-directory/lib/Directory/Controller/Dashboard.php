<?php
class Sabai_Addon_Directory_Controller_Dashboard extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitable = false;
        
        // Init variables
        $sortable_headers = array('title', 'directory', 'claimed', 'featured', 'date', array('name' => 'views', 'title' => __('Sort by Views', 'sabai-directory')), array('name' => 'leads', 'title' => __('Sort by Leads', 'sabai-directory')));
        $sort = $context->getRequest()->asStr('sort', 'date', array('title', 'directory', 'claimed', 'featured', 'date', 'leads', 'views'));
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $url_params = array('sort' => $sort, 'order' => $order);
        $directory_addons = $this->Directory_DirectoryList('addon');
        
        $listings = $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_type', 'directory_listing')
            ->propertyIsIn('post_status', array(Sabai_Addon_Content::POST_STATUS_PENDING, Sabai_Addon_Content::POST_STATUS_PUBLISHED))
            ->startCriteriaGroup('OR')
                ->fieldIs('directory_claim', $this->getUser()->id, 'claimed_by')
                ->startCriteriaGroup()
                    ->fieldIsNull('directory_claim', 'claimed_by')
                    ->propertyIs('post_user_id', $this->getUser()->id)
                ->finishCriteriaGroup()
            ->finishCriteriaGroup();
        if ($sort === 'claimed') {
            $listings->sortByField('directory_claim', $order, 'expires_at');
        } elseif ($sort === 'featured') {
            $listings->sortByField('content_featured', $order, 'expires_at');
        } elseif ($sort === 'leads') {
            $listings->sortByField('content_children_count', $order)
                ->fieldIs('content_children_count', 'directory_listing_lead', 'child_bundle_name');
        } elseif ($sort === 'directory') {
            $listings->sortByProperty('post_entity_bundle_name', $order);
        } elseif ($sort === 'title') {
            $listings->sortByProperty('post_title', $order);
        } elseif ($sort === 'views') {
            $listings->sortByProperty('post_views', $order);
        } else {
            $listings->sortByProperty('post_published', $order);
        }
        
        // Init form
        $form = array(
            'entities' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'title' => __('Title', 'sabai-directory'),
                    'directory' => __('Directory', 'sabai-directory'),
                    'date' => _x('Date', 'date created', 'sabai-directory'),
                    'claimed' => __('Claimed', 'sabai-directory'),
                    'featured' => __('Featured', 'sabai-directory'),
                    'views' => '<i class="fa fa-eye"></i>',
                    'leads' => '<i class="fa fa-comment"></i>',
                    'actions' => '',
                ),
                '#options' => array(),
                '#disabled' => true,
            ),
        );
        
        $this->_makeTableSortable($context, $form['entities'], $sortable_headers, array(), $sort, $order, $url_params);

        $pager = $listings->paginate($context->getRequest()->asInt('limit', 20, array(20, 30, 50, 100)))
            ->setCurrentPage($url_params[Sabai::$p] = $context->getRequest()->asInt(Sabai::$p, 1));
        $directory_links = $unclaimed_listing_ids = array();
        $has_lead = $has_view = false;
        foreach ($pager->getElements() as $listing) {
            $is_claimed = !empty($listing->directory_claim);
            $expired = $is_claimed
                ? (!empty($listing->directory_claim[0]['expires_at']) && $listing->directory_claim[0]['expires_at'] < time())
                : null; 
            if ($listing->isPublished()) {
                // Get rating
                $rating = empty($listing->voting_rating['']['count']) ? '' : sprintf(
                    '%s<span class="sabai-voting-rating-average">%s</span><span class="sabai-voting-rating-count">(%d)</span>',
                    $this->Voting_RenderRating($listing),
                    number_format($listing->voting_rating['']['average'], 2),
                    $listing->voting_rating['']['count']
                );
                if (!$expired && ($is_claimed || $this->HasPermission($listing->getBundleName() . '_edit_own'))) {
                    $listing_title = $this->Entity_Link($listing, array(), '/edit', array('dashboard' => true));
                } else {
                    $listing_title = $listing->getTitle();
                    $listing_title = strlen($listing_title) ? Sabai::h($listing_title) : __('Untitled', '@@sabai_package_name@@');
                }
            } else {
                $rating = '';
                $listing_title = $listing->getTitle();
                $listing_title = $this->Content_StatusLabel($listing) . ' ' . (strlen($listing_title) ? Sabai::h($listing_title) : __('Untitled', 'sabai-directory'));
            }
            $actions = $this->_getActions($listing, $is_claimed, $expired);
            // Claimed?
            if (!isset($listing->directory_claim)) {
                $claimed = '<span class="sabai-label sabai-label-default">' . __('No', 'sabai-directory') . '</span>';
                $unclaimed_listing_ids[] = $listing->getId();
            } else {
                if (empty($listing->directory_claim[0]['expires_at'])) { // never expires
                    $claimed = '<span class="sabai-label sabai-label-success">' . __('Yes', 'sabai-directory') . '</span>';
                } elseif ($listing->directory_claim[0]['expires_at'] < time()) { // expired
                    $claimed = '<span class="sabai-label sabai-label-danger">' . $this->getPlatform()->getHumanTimeDiff($listing->directory_claim[0]['expires_at']) . '</span>';
                } elseif ($listing->directory_claim[0]['expires_at'] < time() + 604800) { // expires in 7 days
                    $claimed = '<span class="sabai-label sabai-label-warning">' . $this->getPlatform()->getHumanTimeDiff($listing->directory_claim[0]['expires_at']) . '</span>';
                } else {
                    $claimed = '<span class="sabai-label sabai-label-success">' . $this->getPlatform()->getHumanTimeDiff($listing->directory_claim[0]['expires_at']) . '</span>';
                }
            }
            // Featured?
            if ($listing->isFeatured()) {
                if (empty($listing->content_featured[0]['expires_at'])) { // never expires
                    $featured = '<span class="sabai-label sabai-label-success">' . __('Yes', 'sabai-directory') . '</span>';
                } elseif ($listing->content_featured[0]['expires_at'] < time()) { // expired
                    $featured = '<span class="sabai-label sabai-label-default">' . __('No', 'sabai-directory') . '</span>';
                } elseif ($listing->content_featured[0]['expires_at'] < time() + 259200) { // expires in 3 days
                    $featured = '<span class="sabai-label sabai-label-warning">' . $this->getPlatform()->getHumanTimeDiff($listing->content_featured[0]['expires_at']) . '</span>';
                } else {
                    $featured = '<span class="sabai-label sabai-label-success">' . $this->getPlatform()->getHumanTimeDiff($listing->content_featured[0]['expires_at']) . '</span>';
                }
            } else {
                $featured = '<span class="sabai-label sabai-label-default">' . __('No', 'sabai-directory') . '</span>';
            }
            
            if (!isset($directory_links[$listing->getBundleName()])) {
                $listing_addon = $this->Entity_Addon($listing);
                $directory_links[$listing->getBundleName()] = '<a href="'. $this->Url('/' . $listing_addon->getSlug('directory')) .'">' . Sabai::h($listing_addon->getTitle('directory')) . '</a>';
            }
            $lead_count = @$listing->content_children_count[0]['directory_listing_lead'];
            if ($lead_count && !$has_lead) {
                $has_lead = true;
            }
            if (!$has_view && $listing->getViews()) {
                $has_view = true;
            }

            $form['entities']['#options'][$listing->getId()] = array(
                'title' => '<strong class="sabai-row-title">' . $listing_title . '</strong> ' . $rating,
                'directory' => $directory_links[$listing->getBundleName()],
                'claimed' => $claimed,
                'featured' => $featured,
                'leads' => $lead_count ? $this->LinkTo($lead_count, $this->Url('/' . $this->getAddon('Directory')->getSlug('dashboard') . '/leads', array('listing_id' => $listing->getId()))) : '',
                'actions' => !empty($actions) ? $this->DropdownButtonLinks($actions, array('size' => 'sm'), null, false, true, true) : '',
                'date' => $this->Date($listing->getTimestamp()),
                'views' => $listing->getViews() ? $listing->getViews() : '',
            );
        }
        // Remove directory column if only 1 directory
        if (count($directory_addons) <= 1) {
            unset($form['entities']['#header']['directory']);            
        }
        // Remove leads/views column if none 
        if (!$has_lead) {
            unset($form['entities']['#header']['leads']);  
        }
        if (!$has_view) {
            unset($form['entities']['#header']['views']);  
        }
        $form['entities']['#row_attributes']['@all']['title']['style'] = 'width:40%;';
        
        // Add link to submit listings if the user has the permission to submit listings to any of the directories
        $links = array(0 => array());
        foreach (array_keys($directory_addons) as $directory_addon) {
            $bundle_name = $this->getAddon($directory_addon)->getListingBundleName();
            if ($this->HasPermission($bundle_name . '_add')) {
                $links[0][] = $this->LinkTo(
                    $this->getAddon($directory_addon)->getTitle('directory'),
                    $this->Url('/' . $this->getAddon('Directory')->getSlug('add-listing'), array('bundle' => $bundle_name))
                );
            }
        }
        if ($count = count($links[0])) {
            if ($count > 1) {
                usort($links[0], array($this, 'sortLinks'));
                array_unshift($links[0], $this->LinkTo(
                    __('Add Listing', 'sabai-directory'),
                    $this->Url('/' . $this->getAddon('Directory')->getSlug('add-listing')),
                    array(),
                    array('class' => 'sabai-btn-primary')
                ));
            } else {
                $links[0] = $links[0][0];
                $links[0]->setLabel(__('Add Listing', 'sabai-directory'))
                    ->setAttribute('class', 'sabai-btn-primary');
            }
        }
        
        $context->addTemplate('directory_dashboard')->setAttributes(array(
            'paginator' => $pager,
            'links' => $this->Filter('directory_dashboard_links', $links, array($pager)),
            'filters' => array(),
            'url_params' => $url_params,
        ));
        
        return $form;
    }
    
    public function sortLinks($a, $b)
    {
        return strcasecmp($a->getLabel(), $b->getLabel());
    }
    
    protected function _getActions($listing, $isClaimed, $expired)
    {
        $actions = array();
        if ($listing->isPublished()) {
            $actions['view'] = array(
                'url' => $this->Entity_Url($listing),
                'icon' => 'eye',
                'title' => __('View listing', 'sabai-directory'),
            );
            if (!$isClaimed) {
                $actions['claim'] = array(
                    'url' => $this->Entity_Url($listing, '/' . $this->Entity_Addon($listing)->getSlug('claim'), array('dashboard' => true)),
                    'icon' => 'check',
                    'title' => __('Claim listing', 'sabai-directory'),
                );
            } else {
                if ($this->HasPermission($listing->getBundleName() . '_trash_own_claimed')) {
                    $actions['delete'] = array(
                        'url' => $this->Entity_Url($listing, '/delete', array('dashboard' => true)),
                        'icon' => 'trash-o',
                        'title' => __('Delete listing', 'sabai-directory'),
                        'modal' => true,
                    );
                }
            }
        }
        $actions = $this->Filter('directory_dashboard_listing_actions', $actions, array($this->Entity_Bundle($listing), $listing, $this->getUser()->getIdentity(), $expired));
        
        if (!empty($actions)) {
            $actions = array('' => array('url' => '#', 'icon' => 'cog', 'title' => '')) + $actions;
            foreach ($actions as $k => $action) {
                $attr = array('title' => $action['title']);
                $method = !empty($action['modal']) ? 'LinkToModal' : 'LinkTo';
                $actions[$k] = $this->$method(
                    isset($action['label']) ? $action['label'] : $action['title'],
                    $action['url'],
                    array('icon' => $action['icon'], 'width' => 600),
                    $attr
                );
            }
        }
        
        return $actions;
    }
}
