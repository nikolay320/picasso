<?php
class Sabai_Addon_Directory_Controller_SearchForm extends Sabai_Controller
{ 
    protected function _doExecute(Sabai_Context $context)
    {
        $addon = $this->_getAddon($context);
        if (empty($context->action_url)) {
            $context->action_url = $this->Url('/' . $addon->getSlug('directory'));
            $context->category_bundle = $addon->getCategoryBundleName();
        } else {
            $bundles = $category_bundles = array();
            if ($context->addons) {
                $addons = array_map('trim', explode(',', $context->addons));
                foreach ($addons as $addon_name) {
                    try {
                        $_addon = $this->getAddon($addon_name);
                    } catch (Sabai_IException $e) {
                        $this->LogError($e);
                        continue;
                    }
                    if (!$_addon instanceof Sabai_Addon_Directory) {
                        continue;
                    }
                    $bundles[] = $_addon->getListingBundleName();
                    $category_bundles[$_addon->getCategoryBundleName()] = $_addon->getTitle('directory');
                }
            }
            if (empty($bundles)) {
                $context->category_bundle = $this->Directory_DirectoryList('category');
            } else {
                $context->bundles = $bundles;
                $context->category_bundle = $category_bundles;
            }
        }
        $context->search = array('no_loc' => !empty($context->no_loc), 'no_key' => !empty($context->no_key), 'no_cat' => !empty($context->no_cat)) + $addon->getConfig('search');
        $context->search['form_type'] = $this->_getFormType($context->search['no_key'], $context->search['no_loc'], $context->search['no_cat']);
        if (!isset($context->distance_mode)) {
            $context->is_mile = (isset($context->distance_mode) ? $context->distance_mode : $addon->getConfig('map', 'distance_mode')) === 'mil';
        }
        if (!isset($context->button) || !in_array(substr($context->button, 10), array('success', 'warning', 'danger', 'info', 'primary'))) {
            $context->button = 'sabai-btn-primary';
        }
        if (!isset($context->address)) $context->address = '';
        if (!isset($context->keywords)) $context->keywords = '';
        if (!isset($context->category)) $context->category = null;
        $context->addTemplate('directory_searchform');
        
        // Load JS files
        if ($this->isAddonLoaded('GoogleMaps')) {
            $this->GoogleMaps_LoadApi(array('autocomplete' => true));
            $this->LoadJqueryUi(array('slider'));
        }
        if ((empty($context->search['no_key']) && $addon->getConfig('search', 'auto_suggest'))
            || empty($context->search['no_loc'])
        ) {
            $this->LoadJs('typeahead.bundle.min.js', 'twitter-typeahead', 'jquery');
        }
    }
    
    protected function _getFormType($noKey = false, $noLoc = false, $noCat = false)
    {
        $ret = 0;
        foreach (array('noKey' => 4, 'noLoc' => 2, 'noCat' => 1) as $key => $value) {
            if (empty(${$key})) $ret += $value;
        }
        return $ret;
    }
    
    protected function _getAddon(Sabai_Context $context)
    {
        $addon = null;
        if (isset($context->addon)) {
            try {
                $addon = $this->getAddon($context->addon);
            } catch (Sabai_IException $e) {
                $this->LogError($e);
            }
        }
        if (!$addon instanceof Sabai_Addon_Directory) {
            $addon = $this->getAddon('Directory');
        }
        return $addon;
    }
}
