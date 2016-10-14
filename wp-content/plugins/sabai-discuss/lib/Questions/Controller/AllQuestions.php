<?php
require_once dirname(__FILE__) . '/Questions.php';
class Sabai_Addon_Questions_Controller_AllQuestions extends Sabai_Addon_Questions_Controller_Questions
{
    protected $_addon, $_allAddons = false, $_addons = array(), $_categoryBundles = array(),
        $_requestedCategory, $_originalAddonNames, $_originalCategoryBundles, $_parentCategory;
    
    protected function _doExecute(Sabai_Context $context)
    {
        if ($addons = $context->getRequest()->asStr('addons', isset($context->addons) ? $context->addons : '')) {
            $addons = array_map('trim', explode(',', $addons));
            foreach ($addons as $addon_name) {
                try {
                    $addon = $this->getAddon($addon_name);
                } catch (Sabai_IException $e) {
                    $this->LogError($e);
                    continue;
                }
                if (!$addon instanceof Sabai_Addon_Questions
                    || !$addon->isAllowedAccess()
                ) {
                    continue;
                }
                $this->_addons[$addon->getQuestionsBundleName()] = $addon_name;
                $this->_categoryBundles[$addon->getCategoriesBundleName()] = $addon->getTitle('questions');
            }
        } else {
            $this->_allAddons = true;
            $addon = $this->getAddon('Questions');
            $this->_addons = array($addon->getQuestionsBundleName() => 'Questions');
            $this->_categoryBundles = array($addon->getCategoriesBundleName() => $addon->getTitle('questions'));
            // Fetch cloned questions add-ons
            foreach ($this->getModel('Addon', 'System')->parentAddon_is('Questions')->fetch()->getArray('name') as $addon_name) {
                $addon = $this->getAddon($addon_name);
                if ($addon->isAllowedAccess()) {
                    $this->_addons[$addon->getQuestionsBundleName()] = $addon_name;
                    $this->_categoryBundles[$addon->getCategoriesBundleName()] = $addon->getTitle('questions');
                } else {
                    $this->_allAddons = false;
                }
            }
        }
        
        if (empty($this->_addons)) return false;
        
        $default_settings_addon = count($this->_addons) > 1 ? 'Questions' : current(array_values($this->_addons));
        
        if (($category = $context->getRequest()->asStr('category'))
            && !is_numeric($category)
        ) { // directory has been selected instead of a category
            $context->getRequest()->set('category', 0);
            $this->_requestedCategory = $category;
            $this->_originalCategoryBundles = $this->_categoryBundles;
            $this->_originalAddonNames = $this->_addons;
            $addon = $this->Entity_Addon($category);
            $this->_addons = array($addon->getQuestionsBundleName() => $addon->getName());
            $this->_categoryBundles = array($category => $addon->getTitle('questions'));
            $this->_allAddons = false;
            $default_settings_addon = $addon->getName();
        }
        
        $this->_addon = $context->getRequest()->asStr('addon', isset($context->addon) ? $context->addon : $default_settings_addon);
        
        parent::_doExecute($context);        
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        if (count($this->_addons) === 1) return $this->Entity_Bundle(current(array_keys($this->_addons)));
        
        if ($this->_allAddons && count($this->_categoryBundles) === 1) {
            return $this->Entity_Bundle($this->Entity_Addon(current(array_keys($this->_categoryBundles)))->getQuestionsBundleName());
        }
        
        if (empty($this->_settings['category'])) return;

        if (!$category = $this->Entity_Entity('taxonomy', $this->_settings['category'], false)) {
            // Invalid category
            $this->_settings['category'] = 0;
            return;
        }
        return $this->Entity_Bundle($this->Entity_Addon($category)->getQuestionsBundleName());
    }
    
    protected function _createQuestionsQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if (!empty($this->_settings['user_id'])) {
            $query->propertyIs('post_user_id', $this->_settings['user_id']);
        }
        if (empty($this->_settings['keywords'][0])) { // no need to search answers
            return $this->_allAddons
                ? $query->propertyIs('post_entity_bundle_type', 'questions')
                : (isset($bundle) ? $query->propertyIs('post_entity_bundle_name', $bundle->name) : $query->propertyIsIn('post_entity_bundle_name', array_keys($this->_addons)));
        }
        if ($this->_allAddons) {
            return $query->propertyIsIn('post_entity_bundle_type', array('questions', 'questions_answers'));
        }
        if (isset($bundle)) {
            return $query->propertyIsIn('post_entity_bundle_name', array($bundle->name, $this->getAddon($bundle->addon)->getAnswersBundleName()));
        }
        $bundles = array_keys($this->_addons);
        foreach ($this->_addons as $addon) {
            $bundles[] = $this->getAddon($addon)->getAnswersBundleName();
        }
        return $query->propertyIsIn('post_entity_bundle_name', $bundles);
    }
    
    protected function _getDefaultSettings(Sabai_Context $context)
    {
        $parent_category = 0;
        if (isset($context->category)) {
            if (count($this->_categoryBundles) === 1) {
                $category_bundles = array_keys($this->_categoryBundles);
                $category_bundle = array_shift($category_bundles);
            } else {
                $category_bundle = 'questions_categories';
            }
            if ($category = $this->getModel('Term', 'Taxonomy')->entityBundleName_is($category_bundle)->name_is($context->category)->fetchOne()) {
                $this->_parentCategory = $category->id;
            } else {
                $category_bundle = $this->_categoryBundles;
            }
        } else {
            $category_bundle = isset($this->_originalCategoryBundles) ? $this->_originalCategoryBundles : $this->_categoryBundles;
        }
        $settings = $this->_getCustomSettings($context) + array('category_bundle' => $category_bundle) + $this->_getAddonSettings($context, $this->_addon);
        // Keep directory selection 
        if (isset($this->_requestedCategory)) {
            $settings['requested_category'] = $this->_requestedCategory;
        }
        
        return $settings;
    }
    
    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $ret = parent::_getUrlParams($context, $bundle) + array(
            'hide_searchbox' => (int)@$this->_settings['hide_searchbox'],
            'hide_nav' => (int)@$this->_settings['hide_nav'],
            'hide_pager' => (int)@$this->_settings['hide_pager'],
            'featured_only' => (int)@$this->_settings['featured_only'],
            'feature' => (int)@$this->_settings['feature'],
        );
        if ($this->_addon !== 'Questions') {
            $ret['addon'] = $this->_addon;
        }
        if (isset($this->_originalAddonNames)) {
            $ret['addons'] = implode(',', $this->_originalAddonNames);
        } elseif (!$this->_allAddons) {
            $ret['addons'] = implode(',', $this->_addons);
        }
        if (isset($this->_settings['perpage'])) {
            $ret['perpage'] = $this->_settings['perpage'];
        }
        if (!empty($this->_settings['user_id'])) {
            $ret['user_id'] = $this->_settings['user_id'];
        }
        // Keep questions section selection 
        if (isset($this->_requestedCategory)) {
            $ret['category'] = $this->_requestedCategory;
        }
        // Keep initial category if set
        if (isset($this->_parentCategory)) {
            $ret['_category'] = $this->_parentCategory;
        }
        return $ret;
    }
    
    protected function _getCustomSettings(Sabai_Context $context)
    {
        $settings = array(
            'hide_searchbox' => $context->getRequest()->asBool('hide_searchbox', !empty($context->hide_searchbox)),
            'hide_nav' => $context->getRequest()->asBool('hide_nav', !empty($context->hide_nav)),
            'hide_pager' => $context->getRequest()->asBool('hide_pager', !empty($context->hide_pager)),
            'featured_only' => $context->getRequest()->asBool('featured_only', !empty($context->featured_only)),
            'feature' => $context->getRequest()->asBool('feature', isset($context->feature) ? $context->feature : 1),
        );
        if (isset($context->perpage)) {
            $settings['perpage'] = $context->perpage;
        } elseif ($context->getRequest()->has('perpage')) {
            $settings['perpage'] = $context->getRequest()->asInt('perpage');
        }
        if (isset($context->sort)) {
            $settings['sort'] = $context->sort;
        }
        $settings['user_id'] = $context->getRequest()->asInt('user_id', isset($context->user_id) ? $context->user_id : null);
        return $settings;
    }
    
    protected function _isFilterRequested(Sabai_Context $context)
    {
        return $context->filter ? $context->filters : parent::_isFilterRequested($context);
    }
    
    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null, array $urlParams = array())
    {
        $links = parent::_getLinks($context, $sort, $bundle);
        if (!$this->_settings['hide_searchbox']
            || $this->_settings['hide_nav']
            || count($this->_settings['category_bundle']) === 1
            || $this->_isFilterRequested($context)
        ) return $links;
        
        if (empty($this->_settings['category_bundle'])) return $links;

        if (isset($this->_originalAddonNames)) $urlParams['addons'] = implode(',', $this->_originalAddonNames);
        
        $options = array('target' => '.sabai-questions-container');
        $links[0] = array($this->_application->LinkToRemote(
            __('All questions', 'sabai-discuss'),
            $context->getContainer(),
            $this->Url($context->getRoute(), array('category' => 0) + $urlParams),
            $options
        ));
        foreach ($this->_settings['category_bundle'] as $category_bundle => $title) {
            $links[0][] = $this->_application->LinkTo(
                $title,
                $this->Url(array('script_url' => '', 'params' => array('category' => $category_bundle) + $urlParams)),
                $this->_requestedCategory === $category_bundle ? $options + array('active' => true) : $options
            );
        }
        
        return $links;
    }
    
    protected function _getDefaultCategoryId(Sabai_Context $context)
    {
        return isset($this->_parentCategory) ? $this->_parentCategory : $context->getRequest()->asInt('_category');
    }
}
