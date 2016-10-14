<?php
require_once dirname(__FILE__) . '/Listings.php';
class Sabai_Addon_Directory_Controller_AllListings extends Sabai_Addon_Directory_Controller_Listings
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
                if (!$addon instanceof Sabai_Addon_Directory) {
                    continue;
                }
                $this->_addons[$addon->getListingBundleName()] = $addon_name;
                $this->_categoryBundles[$addon->getCategoryBundleName()] = $addon->getTitle('directory');
            }
        }
        
        if (empty($this->_addons)) {
            $this->_categoryBundles = $this->Directory_DirectoryList('category');
            $this->_allAddons = true;
            $default_settings_addon = 'Directory';
        } else {
            $default_settings_addon = count($this->_addons) > 1 ? current(array_values($this->_addons)) : 'Directory';
        }
        
        if (($category = $context->getRequest()->asStr('category'))
            && !is_numeric($category)
        ) { // directory has been selected instead of a category
            $context->getRequest()->set('category', 0);
            $this->_requestedCategory = $category;
            $this->_originalCategoryBundles = $this->_categoryBundles;
            $this->_originalAddonNames = $this->_addons;
            $addon = $this->Entity_Addon($category);
            $this->_addons = array($addon->getListingBundleName() => $addon->getName());
            $this->_categoryBundles = array($category => $addon->getTitle('directory'));
            $this->_allAddons = false;
            $default_settings_addon = $addon->getName();
        }
        
        $this->_addon = $context->getRequest()->asStr('addon', isset($context->addon) ? $context->addon : $default_settings_addon);
        
        parent::_doExecute($context);
        
        if (count($this->_settings['category_bundle']) > 1) {
            $context->no_ajax_submit = true;
            $context->action_url = ''; // submit to the current page
        }
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        if (count($this->_addons) === 1) return $this->Entity_Bundle(current(array_keys($this->_addons)));
        
        if ($this->_allAddons && count($this->_categoryBundles) === 1) {
            return $this->Entity_Bundle($this->Entity_Addon(current(array_keys($this->_categoryBundles)))->getListingBundleName());
        }
        
        if (empty($this->_settings['category'])) return;

        if (!$category = $this->Entity_Entity('taxonomy', $this->_settings['category'], false)) {
            // Invalid category
            $this->_settings['category'] = 0;
            return;
        }
        return $this->Entity_Bundle($this->Entity_Addon($category)->getListingBundleName());
    }
    
    protected function _createListingsQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if (!empty($this->_settings['user_id'])) {
            $query->startCriteriaGroup('OR')
                ->startCriteriaGroup()
                    ->propertyIs('post_user_id', $this->_settings['user_id'])
                    ->fieldIsNull('directory_claim', 'claimed_by')
                ->finishCriteriaGroup()
                ->startCriteriaGroup()
                    ->fieldIs('directory_claim', $this->_settings['user_id'], 'claimed_by')
                    ->fieldIsOrGreaterThan('directory_claim', time(), 'expires_at')
                ->finishCriteriaGroup()
                ->startCriteriaGroup()
                    ->fieldIs('directory_claim', $this->_settings['user_id'], 'claimed_by')
                    ->fieldIs('directory_claim', 0, 'expires_at')
                ->finishCriteriaGroup()
            ->finishCriteriaGroup();
        }
        return $this->_allAddons
            ? $query->propertyIs('post_entity_bundle_type', 'directory_listing')
            : (isset($bundle) ? $query->propertyIs('post_entity_bundle_name', $bundle->name) : $query->propertyIsIn('post_entity_bundle_name', array_keys($this->_addons)));
    }
    
    protected function _getDefaultSettings(Sabai_Context $context)
    {
        if (isset($context->category)) {
            if (count($this->_categoryBundles) === 1) {
                $category_bundles = array_keys($this->_categoryBundles);
                $category_bundle = array_shift($category_bundles);
            } else {
                $category_bundle = 'directory_listing_category';
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
        
        if ($settings['view'] === 'list') {
            if (isset($settings['list_map_show'])) {
                $settings['map']['list_show'] = $settings['list_map_show'];
            }
            if (isset($settings['list_map_height'])) {
                $settings['map']['list_height'] = $settings['list_map_height'];
            }
            if (isset($settings['scroll_list'])) {
                $settings['map']['list_scroll'] = $settings['scroll_list'];
            }
        }
        if (isset($settings['map_type'])) {
            $settings['map']['type'] = $settings['map_type'];
        }
        if (isset($settings['map_height'])) {
            $settings['map']['height'] = $settings['map_height'];
        }
        if (isset($settings['map_style'])) {
            $settings['map']['style'] = $settings['map_style'];
        }
        if (isset($settings['zoom'])) {
            $settings['map']['listing_default_zoom'] = $settings['zoom'];
        }
        if (isset($context->distance)) {
            $settings['search']['radius'] = $settings['distance'] = (int)$context->distance;
        }
        $settings['search']['country'] = isset($context->country) ? $context->country : null;
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
            'hide_nav_views' => (int)@$this->_settings['hide_nav_views'],
            'hide_pager' => (int)@$this->_settings['hide_pager'],
            'featured_only' => (int)@$this->_settings['featured_only'],
            'feature' => (int)@$this->_settings['feature'],
        );
        if (!empty($this->_settings['claimed_only'])) {
            $ret['claimed_only'] = 1;
        }
        if ($this->_addon !== 'Directory') {
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
        if (isset($this->_settings['map_type'])) {
            $ret['map_type'] = $this->_settings['map_type'];
        }
        if (isset($this->_settings['map_height'])) {
            $ret['map_height'] = $this->_settings['map_height'];
        }
        if (isset($this->_settings['map_style'])) {
            $ret['map_style'] = $this->_settings['map_style'];
        }
        if (isset($this->_settings['list_map_show'])) {
            $ret['list_map_show'] = $this->_settings['list_map_show'];
        }
        if (isset($this->_settings['list_map_height'])) {
            $ret['list_map_height'] = $this->_settings['list_map_height'];
        }
        if (isset($this->_settings['scroll_list'])) {
            $ret['scroll_list'] = $this->_settings['scroll_list'];
        }
        if (!empty($this->_settings['user_id'])) {
            $ret['user_id'] = $this->_settings['user_id'];
        }
        // Keep directory selection 
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
            'address' => isset($context->address) ? $context->address : '',
            'address_type' => isset($context->address_type) ? $context->address_type : null,
            'hide_searchbox' => $context->getRequest()->asBool('hide_searchbox', !empty($context->hide_searchbox)),
            'hide_nav' => $context->getRequest()->asBool('hide_nav', !empty($context->hide_nav)),
            'hide_nav_views' => $context->getRequest()->asBool('hide_nav_views', !empty($context->hide_nav_views)),
            'hide_pager' => $context->getRequest()->asBool('hide_pager', !empty($context->hide_pager)),
            'featured_only' => $context->getRequest()->asBool('featured_only', !empty($context->featured_only)),
            'feature' => $context->getRequest()->asBool('feature', isset($context->feature) ? $context->feature : 1),
            'claimed_only' => $context->getRequest()->asBool('claimed_only', !empty($context->claimed_only)),
        );
        if (isset($context->perpage)) {
            $settings['perpage'] = $context->perpage;
        } elseif ($context->getRequest()->has('perpage')) {
            $settings['perpage'] = $context->getRequest()->asInt('perpage');
        }
        if (isset($context->sort)) {
            $settings['sort'] = $context->sort;
        }
        if (isset($context->view)) {
            $settings['view'] = $context->view;
        }
        if (isset($context->keywords)) {
            $settings['keywords'] = $context->keywords;
        }
        if (isset($context->zoom)) {
            $settings['zoom'] = (int)$context->zoom;
        } elseif ($context->getRequest()->has('zoom')) {
            $settings['zoom'] = $context->getRequest()->asBool('zoom');
        }
        if (isset($context->map_type)) {
            $settings['map_type'] = (string)$context->map_type;
        } elseif ($context->getRequest()->has('map_type')) {
            $settings['map_type'] = $context->getRequest()->asStr('map_type');
        }
        if (isset($context->map_height)) {
            $settings['map_height'] = (int)$context->map_height;
        } elseif ($context->getRequest()->has('map_height')) {
            $settings['map_height'] = $context->getRequest()->asInt('map_height');
        }
        if (isset($context->map_style)) {
            $settings['map_style'] = (string)$context->map_style;
        } elseif ($context->getRequest()->has('map_style')) {
            $settings['map_style'] = $context->getRequest()->asStr('map_style');
        }
        if (isset($context->list_map_show)) {
            $settings['list_map_show'] = (bool)$context->list_map_show;
        } elseif ($context->getRequest()->has('list_map_show')) {
            $settings['list_map_show'] = $context->getRequest()->asBool('list_map_show');
        }
        if (isset($context->list_map_height)) {
            $settings['list_map_height'] = (int)$context->list_map_height;
        } elseif ($context->getRequest()->has('list_map_height')) {
            $settings['list_map_height'] = $context->getRequest()->asInt('list_map_height');
        }
        if (isset($context->scroll_list)) {
            $settings['scroll_list'] = (bool)$context->scroll_list;
        } elseif ($context->getRequest()->has('scroll_list')) {
            $settings['scroll_list'] = $context->getRequest()->asBool('scroll_list');
        }
        if (isset($context->is_mile)) {
            $settings['is_mile'] = (bool)$context->is_mile;
        } elseif ($context->getRequest()->has('is_mile')) {
            $settings['is_mile'] = $context->getRequest()->asBool('is_mile');
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

        $options = array('target' => '.sabai-directory-listings-container');
        $links[0] = array($this->_application->LinkToRemote(
            __('All listings', 'sabai-directory'),
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