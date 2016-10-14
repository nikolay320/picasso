<?php
class Sabai_Addon_DirectoryBookmarks extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-directory';
    
    public function isUninstallable($currentVersion)
    {
        return true;
    }
    
    public function systemGetMainRoutes()
    {
        $routes = array(
            '/' . $this->_application->getAddon('Directory')->getSlug('dashboard') . '/bookmarks' => array(
                'controller' => 'Dashboard',
                'title_callback' => true,
                'weight' => 30,
                'priority' => 5,
                'type' => Sabai::ROUTE_TAB,
                'callback_path' => 'dashboard',
                'ajax' => false,
            ),
        );
        foreach ($this->_application->Directory_DirectoryList('addon', true) as $addon_name) {
            $routes += array(
                '/' . $this->_application->getAddon($addon_name)->getSlug('directory') . '/users/:user_name/bookmarks' => array(
                    'controller' => 'UserBookmarks',
                    'title_callback' => true,
                    'callback_path' => 'user',
                    'priority' => 5,
                    'type' => Sabai::ROUTE_TAB,
                    'weight' => 15,
                    'ajax' => false,
                ),
            );
        }
        $routes['/sabai/directory/bookmarks'] = array(
            'controller' => 'AllBookmarks',
            'type' => Sabai::ROUTE_CALLBACK,
            'priority' => 5,
        );
        
        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route){}

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'user':
            case 'dashboard':
                return _x('Bookmarks', 'tab', 'sabai-directory');
        }
    }
    
    public function onContentTypeInfoFilter(&$info, $addonName, $bundleName)
    {
        switch ($info['type']) {
            case 'directory_listing':
            case 'directory_listing_review':
            case 'directory_listing_photo':
                $info['voting_favorite'] = array(
                    'button_enable' => true,
                    'button_label' => __('Bookmark', 'sabai-directory'),
                    'icon' => 'bookmark',
                );
        }
    }
    
    public function onDirectoryWidgetSettingsFilter(&$settings, $widgetName)
    {
        switch ($widgetName) {
            case 'featured':
            case 'related':
            case 'nearby':
                $settings['sort']['#options']['voting_favorite.count'] = __('Number of bookmarks', 'sabai-directory');
            case 'recent':
                $settings['directorybookmarks_show'] = array(
                    '#title' => __('Show number of bookmarks', 'sabai-directory'),
                    '#type' => 'checkbox',
                );
                break;
        }
    }
    
    public function onDirectoryWidgetContentFilter(&$content, $widgetName, $settings)
    {
        switch ($widgetName) {
            case 'featured':
            case 'related':
            case 'nearby':
            case 'recent':
                if (!empty($settings['directorybookmarks_show'])) {
                    foreach (array_keys($content) as $i) {
                        if ($count = (int)$content[$i]['listing']->getSingleFieldValue('voting_favorite', 'count')) {
                            $content[$i]['meta'][] = '<i class="fa fa-bookmark"></i> ' . $count;
                        }
                    }
                }
                break;
        }
    }
    
    public function onDirectoryBookmarksInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->ReloadAddons($this->_application->Directory_DirectoryList('addon', true), $log);
    }
    
    public function onDirectoryBookmarksUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->ReloadAddons($this->_application->Directory_DirectoryList('addon', true), $log);
    }
}