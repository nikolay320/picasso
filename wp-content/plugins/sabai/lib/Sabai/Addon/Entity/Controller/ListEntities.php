<?php
abstract class Sabai_Addon_Entity_Controller_ListEntities extends Sabai_Controller
{
    protected $_paginate = true, $_perPage = 20, $_defaultPage = 1, $_defaultSort, $_displayMode = 'summary', $_template, $_sortContainer,
        $_filter = true, $_filters = array(), $_filterValues, $_filterOnChange = true, $_showFilters = false,
        $_largeScreenSingleRow = true, $_sorts = array(), $_idProperty;
    
    protected function _doExecute(Sabai_Context $context)
    {
        // Init 
        $bundle = $this->_getBundle($context);
        if (false === $bundle) {
            $context->setError();
            return;
        }
        if (!$context->bundle) {
            $context->bundle = $bundle;
        }
        $context->paginator = null;
        // Init sorts
        if ($this->_sorts = $this->Filter('entity_sorts', $this->_getSorts($context, $bundle), array($bundle))) {
            $sort_keys = array_keys($this->_sorts);
            $default_sort = isset($this->_defaultSort) && isset($this->_sorts[$this->_defaultSort]) ? $this->_defaultSort : array_shift($sort_keys);
            $current_sort = $context->getRequest()->asStr('sort', $default_sort, $sort_keys);
        } else {
            $current_sort = null;
        }
        // Init URL params
        $url_params = $url_params_without_filter = isset($current_sort)
            ? $this->_getUrlParams($context, $bundle) + array('sort' => $current_sort)
            : $this->_getUrlParams($context, $bundle);
        // Do filter?
        if ($this->_filter
            && $bundle
            && !empty($bundle->info['filterable'])
            && false !== $this->_getFilterTarget($context, $bundle)
        ) {
            $show_filters = $this->_showFilters || (bool)$this->Cookie('sabai_entity_filter');
            if ($request_params = $this->_isFilterRequested($context)) {
                $filter_requests = $filters = array();
                foreach ($bundle->Filters->with('Field') as $filter) {
                    $filters[$filter->name] = $filter;
                    if (!isset($request_params[$filter->name])) continue;

                    $this->_filters[$filter->name] = $filter;
                    $filter_requests[$filter->name] = $request_params[$filter->name];
                }
                if (!empty($filter_requests)) {
                    // Create filter form
                    $filter_form = $this->Form_Build($this->_getFilterForm($context, $bundle, $filters, $url_params, $request_params));
                    if ($filter_form->submit($filter_requests, true)) { // force submit since there is no form build ID
                        $this->_filterValues = $filter_form->values;
                        foreach ($this->_filters as $filter_name => $filter) {
                            if (!isset($this->_filterValues[$filter_name])
                                || (!$ifilter = $this->Field_FilterImpl($filter->type, true))
                                || !$ifilter->fieldFilterIsFilterable($filter->Field, $filter_name, $filter->settings($ifilter->fieldFilterGetInfo('default_settings')), $this->_filterValues[$filter_name], $request_params)
                            ) {
                                unset($this->_filters[$filter_name], $filter_requests[$filter_name]);
                            }
                        }
                        if (!empty($filter_requests)) {
                            $url_params['filter'] = 1;
                            $url_params += $filter_requests;
                        }
                    } else {
                        $this->_filters = array();
                    }
                }
            }
            
            $filter_form_target = $this->_getFilterFormTarget($context, $bundle);
            $show_filters_link_class = 'sabai-btn sabai-btn-default sabai-btn-sm sabai-toggle sabai-entity-btn-filter';
            $show_filters_link = $this->LinkTo(
                ($filter_count = count($this->_filters)) ? sprintf(__('Filter <span class="sabai-badge">%d</span>', 'sabai'), $filter_count) : __('Filter', 'sabai'),
                '#',
                array('no_escape' => true),
                array('data-toggle-target' => $context->getContainer() . ' ' . $filter_form_target, 'data-toggle-cookie' => 'sabai_entity_filter') + array('class' => $show_filters ? $show_filters_link_class . ' sabai-active' : $show_filters_link_class)
            );
            if (!isset($filter_form)) {
                $filter_form = $this->Form_Build($this->_getFilterForm($context, $bundle, $bundle->Filters->with('Field')->getArray(null, 'name'), $url_params));
            } else {      
                if ($filter_count) {
                    $show_filters_link = $this->ButtonLinks(
                        array(
                            $show_filters_link,
                            $this->LinkToRemote(
                                '<i class="fa fa-remove"></i>',
                                $context->getContainer(),
                                $this->Url($context->getRoute(), $url_params_without_filter, '', '&'),
                                array('no_escape' => true, 'target' => $this->_getFilterTarget($context, $bundle)),
                                array('title' => __('Clear all filters', 'sabai'), 'class' => 'sabai-btn-danger')
                            )
                        ),
                        array('label' => true, 'tooltip' => true)
                    );
                }
            }
        } else {
            $show_filters = false;
            $show_filters_link = null;
        }
        // Create sort links
        $sort_links = array();
        foreach ($this->_sorts as $key => $sort) {
            $options = array('pushState' => true);
            if (!is_array($sort)) {
                $sort = array('label' => $sort);
                $attr = array();
            } else {
                $attr = isset($sort['title']) ? array('title' => $sort['title']) : array();
            }
            if (isset($this->_sortContainer)) {
                $attr['data-container'] = $this->_sortContainer; // this attribute is required for tooltips
                $options['target'] = $this->_sortContainer;
            }
            $options['active'] = $key === $current_sort;
            $sort_links[$key] = $this->LinkToRemote(
                $sort['label'],
                $context->getContainer(),
                $this->Url($context->getRoute(), array('sort' => $key) + $url_params),
                $options,
                $attr
            );
        }
        // Set template
        if (!isset($this->_template)) {
            $this->_template = $bundle ? $bundle->type . '_list' : $context->bundle->type . '_list';
        }
        // Fetch entities
        if ($entities = $this->_getEntities($context, $current_sort, $bundle)) {
            $entities = $this->Entity_Render($this->_getEntityType($context), $entities, $bundle ? $bundle->name : null, $this->_displayMode);
        }
        // Assign context
        $context->addTemplate($this->_template)
            ->setAttributes(array(
                'entities' => $entities,
                'url_params' => $url_params,
                'sorts' => $sort_links,
                'current_sort' => $current_sort,
                'links' => $this->_getLinks($context, $current_sort, $bundle, $url_params),
                'show_filters' => $show_filters,
                'show_filters_link' => $show_filters_link,
                'filter_form' => isset($filter_form) ? $filter_form : null,
            ));
    }
    
    protected function _getFilterForm(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle, array $filters, array $urlParams = array(), array $requestParams = array())
    {
        $form = $this->Entity_FilterForm(
            $bundle->name,
            $context->getContainer(),
            $this->_getFilterTarget($context, $bundle),
            $this->Url($context->getRoute(), $urlParams, '', '&'),
            $filters,
            $requestParams,
            $this->_filterOnChange,
            $this->_largeScreenSingleRow
        );
        // Add button if no auto filtering on change
        if ($form && !$this->_filterOnChange) {
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME] = $this->Form_SubmitButtons();
        }
        return $form;
    }
    
    protected function _isFilterRequested(Sabai_Context $context)
    {
        return $context->getRequest()->asBool('filter') ? $context->getRequest()->getParams() : false;
    }
    
    protected function _getFilterTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        
    }
    
    protected function _getFilterFormTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        
    }
    
    protected function _getEntities(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return $this->_paginate ? $this->_paginateEntities($context, $sort, $bundle) : $this->_fetchEntities($context, $sort, $bundle);
    }
    
    protected function _paginateEntities(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if ((!$context->paginator = $this->_paginate($context, $sort, $bundle))
            || !$context->paginator->getElementCount()
        ) {
            return array();
        }
        return $context->paginator->getElements();
    }
    
    protected function _paginate(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if (!$query = $this->_getQuery($context, $sort, $bundle)) {
            return;
        }
        return $query->paginate($this->_perPage)->setCurrentPage($context->getRequest()->asInt(Sabai::$p, $this->_defaultPage));
    }
    
    protected function _fetchEntities(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return ($query = $this->_getQuery($context, $sort, $bundle)) ? $query->fetch() : array();
    }
    
    protected function _getQuery(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if (!$query = $this->_createQuery($context, $bundle)) {
            return;
        }
        // Filter?
        if (!empty($this->_filters)) {
            foreach ($this->_filters as $filter_name => $filter) {
                if ($ifilter = $this->Field_FilterImpl($filter->type, true)) {
                    $ifilter->fieldFilterDoFilter($query->getFieldQuery(), $filter->Field, $filter_name, $filter->settings($ifilter->fieldFilterGetInfo('default_settings')), $this->_filterValues[$filter_name]);
                }
            }
        }
        // Sort?
        if ($sort) {
            if ($sort === 'random') {
                $query->sortByRandom();
            } elseif (is_array(@$this->_sorts[$sort])) {
                $field_name = isset($this->_sorts[$sort]['field_name']) ? $this->_sorts[$sort]['field_name'] : $sort;
                if (isset($this->_sorts[$sort]['field_type'])) {
                    $field_type = $this->_sorts[$sort]['field_type'];
                } elseif ($bundle
                    && ($field = $this->Entity_Field($bundle->name, $field_name))
                ) {
                    $field_type = $field->getFieldType();
                }
                if ($field_type
                    && ($field_type = $this->Field_TypeImpl($field_type, true))
                    && $field_type instanceof Sabai_Addon_Field_ISortable
                ) {
                    if (strpos($sort, ',')) {
                        $args = explode(',', $sort);
                        array_shift($args); // remove field name part
                        if (is_array($this->_sorts[$sort]) && isset($this->_sorts[$sort]['args'])) {
                            $args += $this->_sorts[$sort]['args'];
                        }
                    } else {
                        $args = is_array($this->_sorts[$sort]) && isset($this->_sorts[$sort]['args']) ? $this->_sorts[$sort]['args'] : null;
                    }
                    $field_type->fieldSortableDoSort($query->getFieldQuery(), $field_name, $args);
                }
            }
        } else {
            $query->getFieldQuery()->sortByProperty($this->_idProperty);
        }
        
        return $query;
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {

    }
    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $sorts = array('random' => __('Random', 'sabai'));
        if ($bundle) {
            $sorts += $this->Entity_SortableFields($bundle->name, true);
        }
        return $sorts;
    }

    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null, array $urlParams = array())
    {
        return array(0 => array(), 1 => array());
    }

    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array();
    }
    
    /**
     *@return Sabai_Addon_Entity_Model_Bundle or null
     */
    protected function _getBundle(Sabai_Context $context)
    {
        
    }
    
    /**
     *@return string 
     */
    abstract protected function _getEntityType(Sabai_Context $context);
}