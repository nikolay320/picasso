<?php
class Sabai_Addon_Taxonomy_Controller_Admin_ListTerms extends Sabai_Addon_Form_Controller
{
    private $_count, $_depths = array();
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Init form
        $form = array(
            'entities' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'title' => __('Title', 'sabai'),
                    'slug' => __('Slug', 'sabai'),
                    'created' => __('Date', 'sabai'),
                ),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            ),
            '#bundle' => $context->taxonomy_bundle,
        );
        // Set submit buttons
        $this->_submitButtons = $this->_getSubmitButtons($context);
        
        $this->countByBundle($context->taxonomy_bundle->name);
        
        // Init variables
        $filters = array(
            'all' => $this->_count ? sprintf(__('All (%d)', 'sabai'), $this->_count) : __('All', 'sabai'),
        );
        $filter = 'all';
        $sortable_headers = array('title', 'created');
        $sort = $context->getRequest()->asStr('sort', '', $sortable_headers);
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $url_params = array('filter' => $filter, 'sort' => $sort, 'order' => $order);
        $form['entities']['#header']['content'] = array('order' => 21, 'label' => $this->Entity_BundleLabel($context->bundle, false));
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['entities'], $sortable_headers, array('created'), $sort, $order, $url_params);
        
        if ($this->_count) {
            $pager = $this->_getPager($context->taxonomy_bundle, $sort, $order, $this->Filter('taxonomy_admin_terms_perpage', 50))
                ->setCurrentPage($url_params[Sabai::$p] = $context->getRequest()->asInt(Sabai::$p, 1));
        
            // Add rows
            foreach ($pager->getElements() as $entity) {
                $entity_path = $context->taxonomy_bundle->getAdminPath() . '/' . $entity->getId();
                $entity_title = $entity->getTitle();
                if (!strlen($entity_title)) {
                    $entity_title = __('(no title)', 'sabai');
                } else {
                    $entity_title = mb_strimwidth($entity_title, 0, 200, '...');
                }
                if (!empty($this->_depths[$entity->getId()])) {
                    $entity_title = str_repeat('&#8212;', $this->_depths[$entity->getId()]) . ' ' . $entity_title;
                }
                $title = $this->LinkTo($entity_title, $this->Url($entity_path));
                $links = array(
                    $this->LinkTo(__('Edit', 'sabai'), $this->Url($entity_path)),
                    $this->LinkToModal(__('Delete', 'sabai'), $this->Url($entity_path . '/delete'), array('width' => 470), array('title' => sprintf(_x('Delete this %s', 'Delete taxonomy term modal window title', 'sabai'), $this->Entity_BundleLabel($context->taxonomy_bundle, true)))),
                    $this->LinkTo(__('View', 'sabai'), $this->Entity_Url($entity)),
                );
                $content_count = (int)$entity->getSingleFieldValue('taxonomy_content_count', $context->bundle->type);
                $form['entities']['#options'][$entity->getId()] = array(
                    'title' => '<strong class="sabai-row-title">' . $title . '</strong> (ID: ' . $entity->getId() . ')<div class="sabai-row-action">' . $this->Menu($links) . '</div>',
                    'created' => $this->getPlatform()->getHumanTimeDiff($entity->getTimestamp()),
                    'slug' => Sabai::h($entity->getSlug()),
                    'content' => $content_count ? $this->LinkTo($content_count, $this->Url($context->bundle->getAdminPath(), array('taxonomy_terms' => array($context->taxonomy_bundle->type => $entity->getId())))) : 0,
                    '#entity' => $entity,
                );
            }       
        }

        foreach ($url_params as $url_param_k => $url_param_v) {
            $form[$url_param_k] = array('#type' => 'hidden', '#value' => $url_param_v);
        }
        
        // Set template
        $context->addTemplate('taxonomy_admin_terms')
            ->setAttributes(array(
                'filters' => $filters,
                'filter' => $filter, 
                'url_params' => $url_params,
                'pager' => isset($pager) ? $pager : null,
                'links' => $this->Filter('taxonomy_admin_terms_links', $this->_getLinks($context), array($context->taxonomy_bundle)),
            ));

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!empty($form->values['entities'])) {
            switch ($form->values['action']) {
                case 'delete':
                    $this->_delete($context, $form->values['entities']);
                    break;
                case 'recount_content':
                    $this->_recountContent($context, $form->values['entities']);
                    break;
            }
        }
        
        $context->setSuccess()
            ->setSuccessUrl($this->Url($context->getRoute(), array('sort' => $form->values['sort'], 'order' => $form->values['order'])));
    }
    
    protected function _delete(Sabai_Context $context, $entityIds)
    {
        $entities = array();
        foreach ($this->Entity_TypeImpl('taxonomy')->entityTypeGetEntitiesByIds($entityIds) as $entity) {
            $entities[$entity->getId()] = $entity;
        }
        if (!empty($entities)) {
            $this->getAddon('Entity')->deleteEntities('taxonomy', $entities);
            $this->getPlatform()->deleteCache('taxonomy_terms_' . $context->taxonomy_bundle->name); // clear taxonomy terms cache
        }
    }
    
    protected function _recountContent(Sabai_Context $context, $entityIds)
    {
        $entities = array();
        foreach ($this->Entity_TypeImpl('taxonomy')->entityTypeGetEntitiesByIds($entityIds) as $entity) {
            $entities[$entity->getId()] = $entity;
        }
        if (!empty($entities)) {
            $this->Taxonomy_UpdateContentCount(array($context->taxonomy_bundle->type => $entities), $context->bundle);
            $this->getPlatform()->deleteCache('taxonomy_terms_' . $context->taxonomy_bundle->name); // clear taxonomy terms cache
        }
    }
    
    protected function _getSubmitButtons(Sabai_Context $context)
    {
        return array(
            'action' => array(
                '#type' => 'select',
                '#options' => array(
                    '' => __('Bulk Actions', 'sabai'),
                    'delete' => __('Delete', 'sabai'),
                    'recount_content' => sprintf(__('Recount %s', 'sabai'), $this->Entity_BundleLabel($context->bundle, false)),
                ),
                '#weight' => 1,
            ),
            'apply' => array(
                '#value' => __('Apply', 'sabai'),
                '#btn_size' => 'mini',
                '#weight' => 10,
            ),
        );
    }
    
    private function _getPager($bundle, $sort, $order, $perPage)
    {
        if (isset($bundle->info['taxonomy_hierarchical'])
            && $bundle->info['taxonomy_hierarchical'] === true
            && $sort === ''
        ) {
            return new SabaiFramework_Paginator_Custom(
                array($this, 'countByBundle'),
                array($this, 'fetchByBundle'),
                $perPage,
                array(),
                array($bundle->name)
            );
        }
        $query = $this->Entity_Query('taxonomy')->propertyIs('term_entity_bundle_name', $bundle->name);
        switch ($sort) {
            case 'created':
                $query->sortByProperty('term_created', $order);
                break;
            default:
                $query->sortByProperty('term_title', $order);
                break;
        }
        return $query->paginate($perPage);
    }
    
    public function countByBundle($bundleName)
    {
        if (!isset($this->_count)) {
            $this->_count = $this->getModel('Term')->entityBundleName_is($bundleName)->count();
        }
        return $this->_count;
    }
    
    public function fetchByBundle($bundleName, $limit, $offset)
    {
        $this->_depths = $this->getModel()->getGateway('Term')->fetchByBundle($bundleName, $limit, $offset);
        return $this->Entity_Entities('taxonomy', array_keys($this->_depths), true, true);
    }
        
    protected function _getLinks(Sabai_Context $context)
    {
        return array(
            $this->LinkTo(
                sprintf(__('Add %s', 'sabai'), $this->Entity_BundleLabel($context->taxonomy_bundle, true)),
                $this->Url($context->taxonomy_bundle->getAdminPath() . '/add'),
                array('no_escape' => true, 'icon' => 'plus'),
                array('class' => 'sabai-btn sabai-btn-primary sabai-btn-sm')
            ),
        );
    }
}
