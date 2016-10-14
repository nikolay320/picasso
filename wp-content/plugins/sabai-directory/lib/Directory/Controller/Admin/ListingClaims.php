<?php
class Sabai_Addon_Directory_Controller_Admin_ListingClaims extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Init form
        $form = array(
            '#bundle' => $context->bundle,
            'claims' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'id' => __('Claim ID', 'sabai-directory'),
                    'date' => __('Claim Date', 'sabai-directory'),
                    'type' => __('Claim Type', 'sabai-directory'),
                    'listing' => __('Listing', 'sabai-directory'),
                    'user' => __('User', 'sabai-directory'),
                    'comment' => __('Comment', 'sabai-directory'),
                    'status' => __('Status', 'sabai-directory'),
                ),
                '#options' => array(),
                '#options_disabled' => array(),
                '#multiple' => true,
            ),
        );
        
        // Set submit buttons
        $this->_submitButtons = array(array('#value' => __('Delete', 'sabai-directory')));
       
        // Init variables
        $status_labels = $this->Directory_ClaimStatusLabels();
        $criteria = $this->getModel(null, 'Directory')->createCriteria('Claim')
            ->entityBundleName_is($context->bundle->name)
            ->status_in(array('pending', 'approved', 'rejected')); 
        $sortable_headers = array('date' => 'created');
        $sort = $context->getRequest()->asStr('sort', 'date', array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $url_params = array('sort' => $sort, 'order' => $order);
        // Init entity ID
        if (($entity_id = $context->getRequest()->asInt('entity_id'))
            && $entity = $this->Entity_Entity('content', $entity_id, false)
        ) {
            $url_params['entity_id'] = $entity_id;
            $criteria->entityId_is($entity_id);
            $context->setInfo($entity->getTitle());
        }
        
        // Get counts by status
        $counts = $this->getModel(null, 'Directory')->getGateway('Claim')->getStatusCountByCriteria($criteria);
        $filters = array('' => $this->LinkToRemote(sprintf(__('All (%d)', 'sabai-directory'), array_sum($counts)), $context->getContainer(), $this->Url($context->getRoute(), $url_params), array(), array('class' => 'sabai-btn sabai-btn-default sabai-btn-xs')));
        foreach ($status_labels as $status => $status_label) {
            if (empty($counts[$status])) continue;
                
            $filters[$status] = $this->LinkToRemote(sprintf(__('%s (%d)', 'sabai-directory'), $status_label, $counts[$status]), $context->getContainer(), $this->Url($context->getRoute(), array('status' => $status) + $url_params), array(), array('class' => 'sabai-btn sabai-btn-default sabai-btn-xs'));            
        }
        $current_status = $context->getRequest()->asStr('status', '', array_keys($filters));
        $filters[$current_status]->setAttribute('class', $filters[$current_status]->getAttribute('class') . ' sabai-active');
        if ($current_status) {
            $url_params['status'] = $current_status;
            $criteria->status_is($current_status);
            unset($form['claims']['#header']['status']);
        }
        if (count($filters) > 1) {  
            $context->claims = array();
            // Paginate claims
            $pager = $this->getModel('Claim')
                ->paginateByCriteria($criteria, 20, $sortable_headers[$sort], $order)
                ->setCurrentPage($context->getRequest()->asInt(Sabai::$p, 1)); 
        
            // Set sortable headers
            $this->_makeTableSortable($context, $form['claims'], array_keys($sortable_headers), array(), $sort, $order, $url_params);

            foreach ($pager->getElements()->with('User')->with('Entity') as $claim) {
                $form['claims']['#options'][$claim->id] = array(
                    'id' => $this->LinkToModal('<strong class="sabai-row-title">' . $claim->getLabel() . '</strong>', $this->Url($context->getRoute() . $claim->id), array('no_escape' => true, 'width' => 470), array('title' => sprintf(__('Claim %s', 'sabai-directory'), $claim->getLabel()))),
                    'date' => $this->Date($claim->created),
                    'type' => $claim->type === 'new' ? __('New listing', 'sabai-directory') : __('Existing listing', 'sabai-directory'),
                    'user' => $this->UserIdentityLinkWithThumbnailSmall($claim->User),
                    'comment' => $this->Summarize($claim->comment, 100),
                    'status' => sprintf('<span class="sabai-label %s">%s</span>', $claim->getStatusLabelClass(), $claim->getStatusLabel()),
                    'listing' => $claim->Entity ? $this->LinkTo($claim->Entity->getTitle(), $this->Entity_Bundle($claim->Entity)->getAdminPath() . '/' . $claim->Entity->getId()) : '',
                );
                if (!$this->_canDeleteClaim($claim)) {
                    $form['claims']['#options_disabled'][] = $claim->id;
                }
                $context->claims[$claim->id] = $claim;
            }
        }
        
        $context->addTemplate('directory_admin_listing_claims')
            ->setAttributes(array(
                'links' => array(),
                'filters' => $filters,
                'paginator' => @$pager,
                'url_params' => $url_params,
            ));
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!empty($form->values['claims'])) {
            foreach ($form->values['claims'] as $claim_id) {
                if (!isset($context->claims[$claim_id])
                    || !$this->_canDeleteClaim($context->claims[$claim_id])
                ) {
                    continue;
                }
                $context->claims[$claim_id]->markRemoved();
            }
            $this->getModel(null, 'Directory')->commit();
        }
        $context->setSuccess();
    }
    
    protected function _canDeleteClaim($claim)
    {
        return in_array($claim->status, array('approved', 'rejected')) || !$claim->Entity;
    }
}
