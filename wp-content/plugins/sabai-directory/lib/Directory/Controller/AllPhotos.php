<?php
require_once dirname(__FILE__) . '/Photos.php';
class Sabai_Addon_Directory_Controller_AllPhotos extends Sabai_Addon_Directory_Controller_Photos
{
    protected $_addon, $_allAddons = false, $_addons = array(), $_userId, $_template = 'directory_listing_photo_list', $_hideNav, $_hidePager;
    
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
                $this->_addons[$addon->getPhotoBundleName()] = $addon_name;
            }
            if (empty($this->_addons)) return false;
        } else {
            $this->_allAddons = true;
            $this->_addons = $this->Directory_DirectoryList('photo', true);
        }
        $this->_addon = $context->getRequest()->asStr('addon', null, $this->_addons);
        $this->_userId = $context->getRequest()->asInt('user_id', isset($context->user_id) ? $context->user_id : null);
        $this->_hideNav = $context->getRequest()->asBool('hide_nav', !empty($context->hide_nav));
        $this->_hidePager = $context->getRequest()->asBool('hide_pager', !empty($context->hide_pager));
        
        parent::_doExecute($context);        
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        if ($this->_addon) return $this->Entity_Bundle($this->getAddon($this->_addon)->getPhotoBundleName());
        
        return count($this->_addons) === 1 ? $this->Entity_Bundle(current(array_keys($this->_addons))) : null;
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
        if ($this->_userId) {
            $query->propertyIs('post_user_id', $this->_userId);
        }
        if (isset($bundle)) {
            return $query->propertyIs('post_entity_bundle_name', $bundle->name);
        }
        
        return $this->_allAddons
            ? $query->propertyIs('post_entity_bundle_type', 'directory_listing_photo')
            : $query->propertyIsIn('post_entity_bundle_name', array_keys($this->_addons));
    }
    
    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $ret = parent::_getUrlParams($context, $bundle);
        if ($this->_hideNav) {
            $ret['hide_nav'] = 1;
        }
        if ($this->_hidePager) {
            $ret['hide_pager'] = 1;
        }
        if ($this->_userId) {
            $ret['user_id'] = $this->_userId;
        }
        if (isset($this->_addon)) {
            $ret['addon'] = $this->_addon;
        }
        if (!$this->_allAddons) {
            $ret['addons'] = implode(',', $this->_addons);
        }
        return $ret;
    }
    
    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null, array $urlParams = array())
    {
        $links = parent::_getLinks($context, $sort, $bundle);
        if (count($this->_addons) === 1) return $links;
        
        $links[0] = array($this->_application->LinkToRemote(
            __('All photos', 'sabai-directory'),
            $context->getContainer(),
            $this->Url($context->getRoute(), array('addon' => '') + $urlParams)
        ));
        foreach ($this->_addons as $addon) {
            $links[0][] = $this->_application->LinkToRemote(
                $this->getAddon($addon)->getTitle('directory'),
                $context->getContainer(),
                $this->Url($context->getRoute(), array('addon' => $addon) + $urlParams),
                isset($this->_addon) && $this->_addon === $addon ? array('active' => true) : array()
            );
        }
        
        return $links;
    }
}