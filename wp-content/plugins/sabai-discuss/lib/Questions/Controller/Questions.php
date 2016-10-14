<?php
class Sabai_Addon_Questions_Controller_Questions extends Sabai_Addon_Content_Controller_ListPosts
{
    protected $_template = 'questions_questions', $_sortContainer = '.sabai-questions-container', $_settings;
    
    protected function _doExecute(Sabai_Context $context)
    {
        $this->_settings = $this->_getDefaultSettings($context);
        $this->_filter = empty($this->_settings['search']['no_filters']);
        $this->_filterOnChange = !empty($this->_settings['search']['filters_auto']);
        $this->_showFilters = !empty($this->_settings['search']['show_filters']);
        $this->_largeScreenSingleRow = empty($this->_settings['search']['filters_top']);
        $this->_perPage = $this->_settings['perpage'];
        $this->_defaultSort = $this->_settings['sort'];        
        if ($keywords = $context->getRequest()->asStr('keywords', $this->_settings['keywords'])) {
            $this->_settings['keywords'] = $this->Keywords($keywords, $this->getAddon()->getConfig('search', 'min_keyword_len'));
        }
        $this->_settings['category'] = $context->getRequest()->asStr('category', $this->_settings['parent_category']);
        $category_suggestions = $this->_getCategorySuggestions();
        parent::_doExecute($context);
        $context->setAttributes(array(
            'settings' => $this->_settings,
            'category_suggestions' => $category_suggestions,
        ));
        // Load partial content if request is ajax
        if (strpos($context->getRequest()->isAjax(), '.sabai-questions-container')) {
            if (!$context->entities) {
                $context->addTemplate('questions_questions_none');
            } else {
                $context->addTemplate('questions_questions_list');
            }
        }
        
        // Load JS files
        if (empty($this->_settings['search']['no_key']) && !empty($this->_settings['search']['auto_suggest'])) {
            $this->LoadJs('typeahead.bundle.min.js', 'twitter-typeahead', 'jquery');
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

    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $params = array();
        if (isset($this->_settings['keywords'][2])) {
            $params['keywords'] = $this->_settings['keywords'][2];
        }
        if (!empty($this->_settings['category'])) {
            $params['category'] = $this->_settings['category'];
        }
        
        return $params;
    }

    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $sorts = parent::_getSorts($context, $bundle) + array(
            'active' => array(
                'label' => __('Recently Active', 'sabai-discuss'),
                'field_name' => 'content_activity',
                'field_type' => 'content_activity',
            ),
            'votes' => array(
                'label' => __('Most Votes', 'sabai-discuss'),
                'field_name' => 'voting_updown',
                'field_type' => 'voting_updown',
            ),
            'answers' => array(
                'label' => __('Most Answers', 'sabai-discuss'),
                'field_name' => 'content_children_count',
                'field_type' => 'content_children_count',
                'args' => array('child_bundle_name' => 'questions_answers'),
            ),
        );
        if (empty($this->_settings['keywords'])) {
            $sorts['views'] = array(
                'label' => __('Most Viewed', 'sabai-discuss'),
                'field_name' => 'content_post_views',
                'field_type' => 'content_post_views',
            );
        }
        return isset($this->_settings['sorts']) ? array_intersect_key($sorts, array_flip($this->_settings['sorts'])) : $sorts;
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {        
        return $this->Questions_Query(
            $this->_createQuestionsQuery($context, $bundle),
            $this->_settings['keywords'],
            isset($this->_settings['category_ids']) ? $this->_settings['category_ids'] : $this->_settings['category'],
            $this->_settings['tag'],
            $this->_settings['feature'],
            $this->_settings['featured_only'],
            $this->_settings['search']['match'] === 'any',
            empty($this->_settings['search']['fields']) ? null : $this->_settings['search']['fields']
        );
    }
    
     protected function _createQuestionsQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if (empty($this->_settings['keywords'][0])) {
            return parent::_createQuery($context, $bundle);
        }
        return $this->Entity_Query('content')
            ->propertyIsIn('post_entity_bundle_name', array($bundle->name, $this->getAddon()->getAnswersBundleName()))
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
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
        return array(
            'perpage' => $config['front']['perpage'],
            'sorts' => isset($config['front']['sorts']) ? $config['front']['sorts'] : null,
            'sort' => $config['front']['sort'],
            'keywords' => array(),
            'parent_category' => $this->_getDefaultCategoryId($context),
            'category_bundle' => $this->getAddon()->getCategoriesBundleName(),
            'feature' => $config['front']['feature'],
            'featured_only' => false,
            'filter' => '',
            'tag' => $this->_getDefaultTagId($context),
            'search' => $config['search'],
        );
    }
    
    protected function _getDefaultCategoryId(Sabai_Context $context)
    {
        return 0;
    }
    
    protected function _getDefaultTagId(Sabai_Context $context)
    {
        return null;
    }
    
    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null, array $urlParams = array())
    {
        if (!isset($bundle) || !empty($this->_settings['hide_askbtn'])) {
            return array();
        }
        if (!empty($this->_settings['parent_category'])) {
            $term_id = $this->_settings['parent_category'];
        } elseif (!empty($this->_settings['tag'])) {
            $term_id = $this->_settings['tag'];
        }
        return array(
            1 => array(
                $this->LinkTo(
                    __('Poser une question', 'sabai-discuss'),
                    $this->Url($bundle->getPath() . '/' . $this->getAddon()->getSlug('ask'), isset($term_id) ? array('term_id' => $term_id) : array()),
                    array('icon' => 'pencil'),
                    array('class' => 'sabai-btn sabai-btn-sm sabai-btn-primary')
                ),
            ),
        );
    }
    
    protected function _getFilterTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return '.sabai-questions-container';
    }
    
    protected function _getFilterFormTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return '.sabai-questions-filters';
    }
}