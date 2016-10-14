<?php
class Sabai_Addon_Directory_Controller_Leads extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if (($lead_id = $context->getRequest()->asInt('lead_id'))
            && ($lead = $this->Entity_Entity('content', $lead_id))
            && ($listing = $this->Content_ParentPost($lead))
            && $this->Directory_IsListingOwner($listing, true)
        ) {
            return $this->_viewSingleLead($context, $lead);
        }
        return $this->_viewLeads($context);
    }
    
    protected function _viewSingleLead(Sabai_Context $context, $lead)
    {
        $this->_submitable = false;
        $this->_cancelUrl = $context->getRoute();
        
        // Init form
        $form = array(
            'lead_id' => array(
                '#type' => 'hidden',
                '#value' => $lead->getId(),
            ),
        );
        
        // Set template for viewing this order
        $context->addTemplate('directory_listing_lead_single')
            ->setAttributes(array(
                'lead' => $lead,
            ));
        
        return $form;
    }
    
    protected function _viewLeads(Sabai_Context $context)
    {
        // Init form
        $form = array(
            'leads' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'message' => __('Message', 'sabai-directory'),
                    'name' => __('Name', 'sabai-directory'),
                    'email' => __('E-mail', 'sabai-directory'),
                    'published' => __('Date', 'sabai-directory'),
                    'listing' => __('Listing', 'sabai-directory'),
                ),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            ),
        );
        
        // Fetch listings for which to fetch leads
        // Init entity ID
        if (($listing_id = $context->getRequest()->asInt('listing_id'))
            && ($listing = $this->Entity_Entity('content', $listing_id, false))
            && $this->Directory_IsListingOwner($listing)
        ) {
            $context->setInfo($listing->getTitle());
            $listing_ids = $listing->getId();
        } else {
            $listing_ids = $this->_getListingIds($context);
        }
        if (empty($listing_ids)) {
            return $form;
        }
        
        // Init variables
        $sortable_headers = array('published' => 'post_published');
        $sort = $context->getRequest()->asStr('sort', 'published', $sortable_headers);
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $url_params = array('sort' => $sort, 'order' => $order);
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['leads'], array_keys($sortable_headers), array('published'), $sort, $order, $url_params);
        
        // Init query
        $query = $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_type', 'directory_listing_lead')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if (is_array($listing_ids)) {
            $query->fieldIsIn('content_parent', $listing_ids);
        } else {
            $query->fieldIs('content_parent', $listing_ids);
        }
        
        // Sort query
        if (isset($sortable_headers[$sort])) {
            $query->sortByProperty($sortable_headers[$sort], $order);
        }
        
        // Query with pagination
        $pager = $query->paginate(20)->setCurrentPage($url_params[Sabai::$p] = $context->getRequest()->asInt(Sabai::$p, 1));

        foreach ($pager->getElements() as $entity) {
            $listing = $this->Content_ParentPost($entity, false);
            if (!$listing) {
                // For some reason the paernt post does not exist
                continue;
            }
            $author = $this->Entity_Author($entity);
            $form['leads']['#options'][$entity->getId()] = array(
                'message' => $this->Summarize($entity->getContent(), 200) . '<div class="sabai-row-action">' . $this->LinkTo(__('View', 'sabai-directory'), $this->Url($context->getRoute(), array('lead_id' => $entity->getId())), array(), array('data-modal-title' => sprintf(__('Message by %s', 'sabai-directory'), $this->Entity_Author($entity)->name))) .  '</div>',
                'published' => $this->getPlatform()->getHumanTimeDiff($entity->getTimestamp()),
                'name' => $this->UserIdentityLink($author),
                'email' => '<a href="mailto:' . Sabai::h($author->email) . '">' . Sabai::h($author->email) . '</a>',
                'listing' => $listing->isPublished() ? $this->Entity_Permalink($listing) : Sabai::h($listing->getTitle()),
            );
        }
        
        // Pass required url parameters as hidden values
        foreach ($url_params as $url_param_k => $url_param_v) {
            $form[$url_param_k] = array('#type' => 'hidden', '#value' => $url_param_v);
        }
        
        // Remove listing column if this is a spccific listing page
        if (!is_array($listing_ids)) {
            unset($form['leads']['#header']['listing']);
        }
        
        $this->_submitButtons = array(
            'action' => array(
                '#type' => 'select',
                '#options' => array(
                    '' => __('Bulk Actions', 'sabai-directory'),
                    'delete' => __('Delete', 'sabai-directory'),
                ),
                '#weight' => 1,
            ),
            'apply' => array(
                '#value' => __('Apply', 'sabai-directory'),
                '#btn_size' => 'sm',
                '#weight' => 10,
            ),
        );
        
        return $form;
    }
    
    protected function _getListingIds(Sabai_Context $context)
    {
        $listing_ids = array();
        $listings = $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_type', 'directory_listing')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->fieldIs('directory_claim', $this->getUser()->id, 'claimed_by')
            ->fetch();
        foreach ($listings as $listing) {
            if ($this->Directory_IsListingOwner($listing, true)) {
                $listing_ids[] = $listing->getId();
            }
        }
        
        return $listing_ids;
    }
    
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!empty($form->values['leads'])) {
            switch ($form->values['action']) {
                case 'delete':
                    $lead_ids = array();
                    foreach ($form->values['leads'] as $lead_id) {
                        if (isset($form->settings['leads']['#options'][$lead_id])) {
                            $lead_ids[] = $lead_id;
                        }
                    }
                    if (!empty($lead_ids)) {
                        $this->Content_TrashPosts($lead_ids, Sabai_Addon_Content::TRASH_TYPE_OTHER, 'Deleted by listing owner');
                    }
            }
        }
        $context->setSuccess();
    }
}
