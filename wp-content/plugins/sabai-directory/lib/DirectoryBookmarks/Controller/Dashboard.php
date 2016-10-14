<?php
class Sabai_Addon_DirectoryBookmarks_Controller_Dashboard extends Sabai_Addon_Content_Controller_FavoritePosts
{
    protected $_template = 'directorybookmarks_bookmarks';
    
    protected function _getBundleNames(Sabai_Context $context)
    {
        return $this->getModel('Bundle', 'Entity')
            ->type_in(array('directory_listing', 'directory_listing_review', 'directory_listing_photo'))
            ->fetch()
            ->getArray('name');
    }
}