<?php
class Sabai_Addon_DirectoryBookmarks_Controller_AllBookmarks extends Sabai_Addon_Content_Controller_FavoritePosts
{
    protected $_addons = array(), $_userId, $_template = 'directorybookmarks_bookmarks';
    
    protected function _getBundleNames(Sabai_Context $context)
    {
        $ret = array();
        foreach ($this->_addons as $addon_name) {
            $addon = $this->getAddon($addon_name);
            $ret[] = $addon->getListingBundleName();
            $ret[] = $addon->getReviewBundleName();
            $ret[] = $addon->getPhotoBundleName();
        }
        return $ret;
    }
    
    protected function _getUserId(Sabai_Context $context)
    {
        return $this->_userId;
    }
    
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$this->_userId = $context->getRequest()->asInt('user_id', isset($context->user_id) ? $context->user_id : null)) {
            return false;
        }
        
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
                $this->_addons[] = $addon_name;
            }
        }
        if (empty($this->_addons)) {
            $this->_addons = array_keys($this->Directory_DirectoryList('addon'));
        }
        
        parent::_doExecute($context);        
    }
    
    protected function _getUrlParams(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $ret = parent::_getUrlParams($context, $bundle);
        $ret['user_id'] = $this->_userId;
        $ret['addons'] = implode(',', $this->_addons);

        return $ret;
    }
}