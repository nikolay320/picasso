<?php
class Sabai_Addon_DirectoryBookmarks_Controller_UserBookmarks extends Sabai_Addon_Content_Controller_FavoritePosts
{
    protected $_template = 'directorybookmarks_bookmarks';
    
    protected function _getBundleNames(Sabai_Context $context)
    {
        $addon = $this->getAddon($context->bundle->addon);
        return array($addon->getListingBundleName(), $addon->getReviewBundleName(), $addon->getPhotoBundleName());
    }
}