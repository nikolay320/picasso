<?php
class Sabai_Addon_System extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_System_IAdminMenus,
               Sabai_Addon_System_IAdminSettings
{
    const VERSION = '1.3.28', PACKAGE = 'sabai'; 
    
    private static $_doneHead = false;

    public function systemGetMainRoutes()
    {
        return array(
            '/sabai' => array(
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/sabai-sitemap-index' => array(
                'controller' => 'SitemapIndex',
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/sabai/user/_autocomplete' => array(
                'controller' => 'UserAutocomplete',
                'type' => Sabai::ROUTE_CALLBACK,
                'access_callback' => true,
                'callback_path' => 'user_autocomplete',
            ),
            '/sabai/user/profile/:user_name' => array(
                'controller' => 'UserProfile',
                'access_callback' => true,
                'callback_path' => 'user_profile',
                'format' => array(':user_name' => '.+'),
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/sabai/iconlist' => array(
                'controller' => 'IconList',
                'type' => Sabai::ROUTE_CALLBACK,
            ),
        );
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'user_autocomplete':
                return !$this->_application->getUser()->isAnonymous();
            case 'user_profile':
                $user_name = $context->getRequest()->asStr('user_name');
                $context->identity = $this->_application->UserIdentityByUsername(rawurldecode($user_name));
                return $context->identity->isAnonymous() ? false : true;
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route){}

    public function systemGetAdminRoutes()
    {
        $routes = array(
            '/settings' => array(
                'controller' => 'Settings',
                'type' => Sabai::ROUTE_TAB,
                'title_callback' => true,
            ),
            '/settings/clearcache' => array(
                'controller' => 'ClearCache',
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/settings/system' => array(
                'controller' => 'SystemSettings',
                'title_callback' => true,
            ),
            '/addons' => array(

            ),
            '/addons/installable/:addon_name/install' => array(
                'controller' => 'InstallAddon',
                'title_callback' => true,
            ),
            '/addons/installable/:addon_name/delete' => array(
                'controller' => 'DeleteAddon',
                'title_callback' => true,
            ),
            '/addons/addon/:addon_name' => array(
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/addons/addon/:addon_name/uninstall' => array(
                'controller' => 'UninstallAddon',
                'title_callback' => true,
            ),
            '/addons/addon/:addon_name/upgrade' => array(
                'controller' => 'UpgradeAddon',
                'title_callback' => true,
            ),
            '/addons/addon/:addon_name/reload' => array(
                'controller' => 'ReloadAddon',
                'title_callback' => true,
            ),
            '/addons/addon/:addon_name/clone' => array(
                'controller' => 'CloneAddon',
                'title_callback' => true,
            ),
            '/addons/upgrade' => array(
                'controller' => 'UpgradeAddons',
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/routes' => array(
                'controller' => 'Routes',
                'title_callback' => true,
            ),
            '/info' => array(
                'controller' => 'Info',
                'title_callback' => true,
            ),
            '/cron' => array(
                'controller' => 'RunCron',
                'title_callback' => true,
            ),
            '/sabai/user/_autocomplete' => array(
                'controller' => 'UserAutocomplete',
                'type' => Sabai::ROUTE_CALLBACK,
            ),
        );
        foreach ($this->_application->getInstalledAddonsByInterface('Sabai_Addon_System_IAdminSettings') as $addon) {
            $routes['/settings/' . strtolower($addon)] = array(
                'controller' => 'AddonSettings',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'addon_settings',
                'data' => array(
                    'addon' => $addon,
                ),
                'priority' => 5,
            );
        }
        
        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case '/addons':
                return true; // for backward compat with 1.0.x
            case '/settings':
                return true;
            case '/addons/addon/:addon_name':
                return ($addon_name = $context->getRequest()->asStr('addon_name'))
                    && $this->_application->isAddonLoaded($addon_name);
            case 'addon_settings':
                if (!$this->_application->isAddonLoaded($route['data']['addon'])) return false;
                $context->addon = $route['data']['addon'];
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case '/settings':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? __('General', 'sabai') : __('Sabai Settings', 'sabai');
            case '/settings/system':
                return __('System Settings', 'sabai');
            case '/addons/addon/:addon_name':
                return $context->getRequest()->asStr('addon_name');
            case '/addons/installable/:addon_name/install':
                return __('Install Add-on', 'sabai');
            case '/addons/installable/:addon_name/delete':
                return __('Delete Add-on', 'sabai');
            case '/addons/addon/:addon_name/uninstall':
                return __('Uninstall Add-on', 'sabai');
            case '/addons/addon/:addon_name/upgrade':
            case '/addons/addon/:addon_name/reload':
                return __('Upgrade Add-on', 'sabai');
            case '/addons/addon/:addon_name/clone':
                return __('Clone Add-on', 'sabai');
            case '/routes':
                return 'Routes';
            case '/info':
                return __('System Information', 'sabai');
            case '/cron':
                return __('Run Cron', 'sabai');
            case 'addon_settings':
                return sprintf(__('Configure %s Add-on', 'sabai'), $this->_application->getAddon($context->addon)->getName());
        }
    }

    public function onSystemIMainRouterInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onSystemIRouterInstalled($addon, false);
    }

    public function onSystemIAdminRouterInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onSystemIRouterInstalled($addon, true);
    }

    private function _onSystemIRouterInstalled(Sabai_Addon $addon, $admin = false)
    {
        $model = $this->getModel();
        if ($admin) {
            $routes = $addon->systemGetAdminRoutes();
            $entity_name = 'Adminroute';
        } else {
            $routes = $addon->systemGetMainRoutes();
            $entity_name = 'Route';
        }

        $root_paths = array();

        // Insert route data
        foreach ($routes as $route_path => $route_data) {
            $route_path = strtolower(rtrim($route_path, '/'));

            // Default to ajax enabled for the user side routes
            if (!isset($route_data['ajax']) && !$admin) $route_data['ajax'] = 1;

            $route = $model->create($entity_name);
            $route->markNew();
            $route->controller = (string)@$route_data['controller'];
            $route->forward = (string)@$route_data['forward'];
            $route->addon = $addon->getName();
            $route->controller_addon = isset($route_data['controller_addon']) ? $route_data['controller_addon'] : $addon->getName();
            $route->type = isset($route_data['type']) ? $route_data['type'] : Sabai::ROUTE_NORMAL;
            $route->path = $route_path;
            $route->title = (string)@$route_data['title'];
            $route->description = (string)@$route_data['description'];
            $route->format = (array)@$route_data['format'];
            $route->method = (string)@$route_data['method'];
            $route->access_callback = !empty($route_data['access_callback']) ? 1 : 0;
            $route->title_callback = !empty($route_data['title_callback']) ? 1 : 0;
            $route->callback_path = isset($route_data['callback_path']) ? $route_data['callback_path'] : $route_path;
            $route->callback_addon = isset($route_data['callback_addon']) ? $route_data['callback_addon'] : $addon->getName();
            $route->weight = isset($route_data['weight']) ? ($route_data['weight'] > 99 ? 99 : $route_data['weight']) : 9;
            $route->depth = substr_count($route_path, '/');
            $route->ajax = intval(@$route_data['ajax']);
            $route->class = (string)@$route_data['class'];
            $route->data = (array)@$route_data['data'];
            if (!isset($route_data['priority'])) {
                // Set lower priority if it is a child route of another plugin
                if (0 !== strpos(str_replace('_', '', $route_path), '/' . strtolower($addon->getName()))) {
                    $route->priority = 3; // default is 5
                }
            } else {
                $route->priority = intval($route_data['priority']);
            }

            if ($root_path = substr($route_path, 0, strpos($route_path, '/', 1))) {
                $root_paths[$root_path] = $root_path;
            }
        }

        $model->commit();

        // Clear cached route data
        foreach ($root_paths as $root_path) {
            $this->_application->getPlatform()->deleteCache('system_' . strtolower($entity_name) . str_replace('/', '_', $root_path));
        }
    }

    public function onSystemIMainRouterUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onSystemIRouterUninstalled($addon, false);
    }

    public function onSystemIAdminRouterUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_onSystemIRouterUninstalled($addon, true);
    }

    private function _onSystemIRouterUninstalled(Sabai_Addon $addon, $admin = false)
    {
        $model = $this->getModel();
        $entity_name = $admin ? 'Adminroute' : 'Route';
        $criteria = $model->createCriteria($entity_name)->addon_is($addon->getName());
        $model->getGateway($entity_name)->deleteByCriteria($criteria);
    }

    public function onSystemIMainRouterUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->reloadRoutes($addon, false);
        $this->_application->getPlatform()->deleteCache('system_sluggable_routes');
    }

    public function onSystemIAdminRouterUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->reloadRoutes($addon, true);
    }
    
    public function onSystemIAdminSettingsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->reloadRoutes($this, true);
    }
    
    public function onSystemIAdminSettingsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->reloadRoutes($this, true);
    }
    
    public function onSystemIAdminSettingsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->reloadRoutes($this, true);
    }
        
    public function onSystemISlugsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('system_slugs');
    }
    
    public function onSystemISlugsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('system_slugs');
    }
        
    public function onSystemISlugsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('system_slugs');
    }

    public function reloadRoutes(Sabai_Addon $addon, $admin = false)
    {
        $this->_onSystemIRouterUninstalled($addon, $admin);
        $this->_onSystemIRouterInstalled($addon, $admin);
        return $this;
    }

    public function onSabaiWebResponseSend(Sabai_Context $context)
    {
        $platform = $this->_application->getPlatform();
        // Add messages saved during the previous request to the response content as flash messages
        if ($flash = $platform->getSessionVar('system_flash', $this->_application->getUser()->id)) {
            $platform->setFlash($flash);
            $platform->deleteSessionVar('system_flash', $this->_application->getUser()->id);
        }
    }
    
    public function onSabaiWebResponseRender(Sabai_Context $context, Sabai_Template $template)
    {
        $platform = $this->_application->getPlatform();
        if ($platform->isAdmin()) return;
        
        // Check if the current theme has a custom template directory
        $custom_assets_dir = $platform->getCustomAssetsDir();
        if (is_dir($custom_assets_dir)) {
            $template->addDir($custom_assets_dir);
        }
        // Add more directories if this is a cloned add-on
        if (isset($context->bundle)
            && $context->bundle instanceof Sabai_Addon_Entity_Model_Bundle
        ) {
            $custom_assets_dir = $custom_assets_dir . '/' . $context->bundle->addon;
            if (is_dir($custom_assets_dir)) {
                $template->addDir($custom_assets_dir);
            }
        }
    }

    public function onSabaiWebResponseRenderHtmlLayout(Sabai_Context $context, &$content)
    {
        $platform = $this->_application->getPlatform();
        
        if (!self::$_doneHead) {        
            $this->_application->LoadJquery();
            $this->_application->LoadJson2();
            $this->_application->LoadJqueryUi(array('effects-highlight'), empty($this->_config['no_ui_css']) || $platform->isAdmin());
            $this->_application->LoadJs('bootstrap.min.js', 'sabai-bootstrap', 'jquery');
            $this->_application->LoadJs('bootstrap-growl.min.js', 'sabai-bootstrap-growl', 'sabai-bootstrap');
            $this->_application->LoadJs('jquery.scrollTo.min.js', 'jquery-scrollto', 'jquery');
            $this->_application->LoadJs('sabai.min.js', 'sabai', 'jquery');
            $this->_application->LoadJs('jquery.autosize.min.js', 'jquery-autosize', 'jquery');
            $this->_application->LoadJs('jquery.coo_kie.min.js', 'jquery-cookie', 'jquery');
            if (empty($this->_config['no_bxslider'])) {
                $this->_application->LoadJs('jquery.bxslider.min.js', 'jquery-bxslider', 'jquery');
                $this->_application->LoadCss('jquery.bxslider.min.css', 'jquery-bxslider');
            }
            $platform->addHeader(sprintf('<script type="text/javascript">var SABAI = SABAI || {}; SABAI.isRTL = %s; SABAI.domain = "%s"; SABAI.path = "%s";</script>', $platform->isLanguageRTL() ? 'true' : ' false', $platform->getCookieDomain(), $platform->getCookiePath()), 'jquery-init');

            $this->_application->LoadCss('font-awesome.min.css', 'sabai-font-awesome');
            if ($platform->isAdmin()) {
                $this->_application->LoadCss('admin.min.css', 'sabai');
                if ($platform->isLanguageRTL()) {
                    $this->_application->LoadCss('admin-rtl.min.css', 'sabai-rtl', 'sabai');
                }
            } else {
                $this->_application->LoadCss('main.min.css', 'sabai');
                if ($platform->isLanguageRTL()) {
                    $this->_application->LoadCss('main-rtl.min.css', 'sabai-rtl', 'sabai');
                }
            }
            // Check for custom stylesheet
            if (@file_exists($platform->getCustomAssetsDir() . '/style.css')) {
                $this->_application->LoadCss($platform->getCustomAssetsDirUrl() . '/style.css', 'sabai-custom', 'sabai', false);
            }
        
            self::$_doneHead = true;
        }
        
        $platform->addJs('SABAI.init($("' . $context->getContainer() . '"));', 'jquery-init-' . $context->getContainer(), true, 0);
    }

    public function onSabaiResponseSendComplete(Sabai_Context $context)
    {
        // Save response messages to session if flashing is enabled
        if ($context->isFlashEnabled() && ($flash = $context->getFlash())) {
            $this->_application->getPlatform()->setSessionVar('system_flash', $flash, $this->_application->getUser()->id);
        }
    }

    public function getMainRoutes($rootPath = '/')
    {
        return $this->_getRoutes($rootPath, 'Route');
    }

    public function getAdminRoutes($rootPath = '/')
    {
        return $this->_getRoutes($rootPath, 'Adminroute');
    }

    private function _getRoutes($rootPath, $entityName)
    {
        $root_path = rtrim($rootPath, '/');

        // Check if already cached
        $cache_id = 'system_' . strtolower($entityName) . str_replace('/', '_', $root_path);
        if ($cache = $this->_application->getPlatform()->getCache($cache_id)) {
            return $cache;
        }

        $ret = array();
        $routes = $this->getModel()->$entityName
            ->path_startsWith($root_path)
            // fetch routes with lower priority first so that the ones with higher priority will overwrite them
            ->fetch(0, 0, 'priority', 'ASC');
        if ($routes->count()) {
            $root_path_dir = dirname($root_path);
            foreach ($routes as $route) {
                if (!$this->_application->isAddonLoaded($route->addon)) continue;
                
                // Initialize route data
                // Any child route data already defined?
                $child_routes = !empty($ret[$route->path]['routes']) ? $ret[$route->path]['routes'] : array();
                $ret[$route->path] = $route->toArray();
                $ret[$route->path]['routes'] = $child_routes;

                $current_path = $route->path;
                while ($root_path_dir !== $parent_path = dirname($current_path)) {
                    $current_base = substr($current_path, strlen($parent_path) + 1); // remove the parent path part

                    if (!isset($ret[$current_path]['path'])) {
                        // Check whether format is defined if dynamic route
                        $format = array();
                        if (0 === strpos($current_base, ':') && isset($ret[$route->path]['format'][$current_base])) {
                            $format = $ret[$route->path]['format'][$current_base];
                            unset($ret[$route->path]['format'][$current_base]);
                        }
                        $ret[$current_path]['path'] = $current_path;
                        $ret[$current_path]['addon'] = $route->addon;
                        $ret[$current_path]['type'] = Sabai::ROUTE_NORMAL;
                        $ret[$current_path]['format'] = !empty($format) ? array($current_base => $format) : array();
                    }
                    if (!isset($ret[$parent_path]['addon'])) $ret[$parent_path]['addon'] = $route->addon;
                    $ret[$parent_path]['routes'][$current_base] = $current_path;

                    $current_path = $parent_path;
                }
            }
        }

        // Allow add-ons to modify routes
        $ret = $this->_application->Filter('system_routes', $ret, array($rootPath, $entityName));
        // Cache routes
        $this->_application->getPlatform()->setCache($ret, $cache_id);

        return $ret;
    }

    public function saveAddonConfig($name, array $config, $merge = true)
    {
        if (!$entity = $this->getModel('Addon')->name_is($name)->fetchOne()) {
            throw new Sabai_RuntimeException('Failed fetching add-on data from the database.');
        }

        $config_non_cacheable = array();
        foreach ($this->_application->getAddon($name)->getNonCacheableConfig() as $config_name) {
            if (array_key_exists($config_name, $config)) {
                $config_non_cacheable[$config_name] = $config[$config_name];
                unset($config[$config_name]);
            }
        }
        $entity->setParams($config, $config_non_cacheable, $merge);
        $entity->commit();

        return $entity;
    }

    public function onSabaiAddonInstalled(Sabai_Addon_System_Model_Addon $addonEntity, ArrayObject $log)
    {
        $addon = $this->_application->getAddon($addonEntity->name);
        
        $this->_invokeAddonEvents($addon, 'installed', 'install_success', $log);
    }

    public function onSabaiAddonUninstalled($addonEntity, ArrayObject $log)
    {
        $addon = $this->_application->getAddon($addonEntity->name);

        $this->_invokeAddonEvents($addon, 'uninstalled', 'uninstall_success', $log);
    }

    public function onSabaiAddonUpgraded($addonEntity, $previousVersion, ArrayObject $log)
    {
        $addon = $this->_application->getAddon($addonEntity->name);
        
        $this->_invokeAddonEvents($addon, 'upgraded', 'upgrade_success', $log, array($previousVersion));
    }
    
    private function _invokeAddonEvents(Sabai_Addon $addon, $event, $event2, ArrayObject $log, array $args = array())
    {
        $parent_addon_name = $addon->hasParent();
        $event_addon_name = strtolower($parent_addon_name ? $parent_addon_name : $addon->getName());
        $args = array_merge(array($addon, $log), $args);
        $this->_application->Action($event_addon_name . '_' . $event, $args);

        // Invoke events for each interface implemented by the addon
        if ($interfaces = class_implements($addon)) { // get interfaces implemented by the plugin
            if ($parent_addon_name) {
                // Interfaces are in reversed order if the addon is a child addon of another addon, so we must reverse them back here
                $interfaces = array_reverse($interfaces);
            }
            // Dispatch event for each interface
            foreach ($interfaces as $interface) {
                if (stripos($interface, 'sabai_addon_') == 0) {
                    // Create event name without the sabai_addon_ prefix and underscores
                    $this->_application->Action(
                        strtolower(substr($interface, strlen('sabai_addon_'))) . '_' . $event,
                        $args
                    );
                }
            }
        }
        
        $this->_application->Action($event_addon_name . $event2, $args);
    }
    
    public function onSystemIPermissionCategoriesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('system_permission_categories');
    }
    
    public function onSystemIPermissionCategoriesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('system_permission_categories');
    }

    public function onSystemIPermissionCategoriesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('system_permission_categories');
    }
    
    public function onSystemIPermissionsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {        
        if ($permissions = $addon->systemGetPermissions()) {
            $this->_createPermissionModels($addon, $permissions);
        
            // Add default permissions to user roles
            if (($default_permissions = $addon->systemGetDefaultPermissions())
                && ($user_roles = $this->_application->getPlatform()->getUserRoles())
            ) {
                foreach ($this->getModel('Role')->name_in($user_roles)->fetch() as $role) {
                    if ($role->isGuest()) continue;
                
                    foreach ($default_permissions as $permission) {
                        $role->addPermission($permission);
                    }
                }
            } 
            $this->getModel()->commit();
        }
        
        $this->_application->getPlatform()->deleteCache('system_permission_categories');
    }

    public function onSystemIPermissionsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_deletePermissionModels($addon);
        $this->getModel()->commit();
        $this->_application->getPlatform()->deleteCache('system_permission_categories');
    }

    public function onSystemIPermissionsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$permissions = $addon->systemGetPermissions()) {
            $this->_deletePermissionModels($addon);
        } else {
            $user_role_names = $this->_application->getPlatform()->getUserRoles();
            $user_roles = $this->getModel('Role')->name_in($user_role_names)->fetch()->getArray();
            $already_installed = array();
            foreach ($this->getModel('Permission')->addon_is($addon->getName())->fetch() as $current) {
                if (!isset($permissions[$current->name])) {
                    $current->markRemoved();
                    foreach ($user_roles as $user_role) {
                        $user_role->removePermission($current->name);
                    }
                } else {
                    $already_installed[$current->name] = $current->name;
                    if ($current->title != $permissions[$current->name]['label']) {
                        $current->title = $permissions[$current->name]['label']; // udpate label
                    }
                    if ($current->description != $permissions[$current->name]['description']) {
                        $current->description = $permissions[$current->name]['description']; // udpate description
                    }
                    if ($current->permissioncategory_name != $permissions[$current->name]['category']) {
                        $current->permissioncategory_name = $permissions[$current->name]['category']; // udpate category
                    }
                    if (isset($permissions[$current->name]['weight']) && $current->weight != $permissions[$current->name]['weight']) {
                        $current->weight = $permissions[$current->name]['weight']; // udpate weight
                    }
                    if ($current->guest_allowed != $permissions[$current->name]['guest_allowed']) {
                        $current->guest_allowed = $permissions[$current->name]['guest_allowed'];
                    }
                }
            }
            if ($new_permissions = array_diff_key($permissions, $already_installed)) {
                $this->_createPermissionModels($addon, $new_permissions);
                
                // Default permissions defined for the default role?
                if ($default_permissions = $addon->systemGetDefaultPermissions()) {
                    foreach ($default_permissions as $permission) {
                        // Assign the permission to default user roles if it is a new permission created just now
                        if (isset($new_permissions[$permission])) {
                            foreach ($user_roles as $user_role) {
                                if ($user_role->isGuest()) {
                                    continue;
                                }
                                $user_role->addPermission($permission);
                            }
                        }
                    }
                } 
                
            }
        }
        $this->getModel()->commit();
        $this->_application->getPlatform()->deleteCache('system_permission_categories');
    }

    private function _createPermissionModels(Sabai_Addon $addon, array $permissions)
    {
        foreach ($permissions as $name => $info) {
            $permission = $this->getModel()->create('Permission');
            $permission->markNew();
            $permission->name = $name;
            $permission->addon = $addon->getName();
            $permission->title = $info['label'];
            $permission->description = isset($info['description']) ? $info['description'] : '';
            $permission->permissioncategory_name = $info['category'];
            $permission->weight = isset($info['weight']) ? $info['weight'] : 10;
            $permission->guest_allowed = !empty($info['guest_allowed']);
        }
    }

    private function _deletePermissionModels(Sabai_Addon $addon)
    {
        $user_role_names = $this->_application->getPlatform()->getUserRoles();
        $user_roles = $this->getModel('Role')->name_in($user_role_names)->fetch()->getArray();
        $permissions = $this->getModel('Permission')->addon_is($addon->getName())->fetch();
        // Remove permissions from the default role
        foreach ($permissions as $permission) {
            foreach ($user_roles as $user_role) {
                $user_role->removePermission($permission->name);
            }
            $permission->markRemoved();
        }
    }
    
    public function reloadPermissions(Sabai_Addon $addon)
    {
        $this->onSystemIPermissionsUpgraded($addon, $log = new ArrayObject());
        return $this;
    }

    public function onSystemInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        foreach ($this->_application->getPlatform()->getUserRoles() as $role_name => $role_title) {
            $role = $this->getModel()->create('Role')->markNew();
            $role->name = $role_name;
            $role->title = $role_title;
        }
        // Create guest role
        $role = $this->getModel()->create('Role')->markNew();
        $role->name = '_guest_';
        $role->title = $this->_application->_t(_n_noop('Guest', 'Guest', 'sabai'));
        // Commit
        $this->getModel()->commit();
    } 
        
    public function getUserMenus()
    {
        $user_menus = array();
        foreach ($this->_application->getInstalledAddonsByInterface('Sabai_Addon_System_IUserMenus') as $addon_name) {
            if (!$this->_application->isAddonLoaded($addon_name)) continue;

            foreach ($this->_application->getAddon($addon_name)->systemGetUserMenus() as $menu_name => $menu_data) {
                $user_menus[(string)@$menu_data['parent']][$menu_name] = $menu_data; 
            }
        }
        return $user_menus;
    }
    
    public function getAdminMenus()
    {
        $ret = array();
        foreach ($this->_application->getInstalledAddonsByInterface('Sabai_Addon_System_IAdminMenus') as $addon_name) {
            if (!$this->_application->isAddonLoaded($addon_name)) continue;

            foreach ($this->_application->getAddon($addon_name)->systemGetAdminMenus() as $menu_path => $menu) {
                if (!isset($ret[$menu_path])) {
                    $ret[$menu_path] = array();
                }
                $ret[$menu_path] += $menu;
                $weight = empty($menu['weight']) ? 0 : intval($menu['weight']);
                $ret[isset($menu['parent']) ? $menu['parent'] : '/']['_children'][$weight][] = $menu_path; 
            }
        }
        foreach (array_keys($ret) as $parent_menu_path) {
            if (!isset($ret[$parent_menu_path]['_children'])) continue;
            foreach (array_keys($ret[$parent_menu_path]['_children']) as $menu_weight) {
                foreach ($ret[$parent_menu_path]['_children'][$menu_weight] as $menu_path) {
                    $ret[$parent_menu_path]['children'][] = $menu_path;
                }
            }
            unset($ret[$parent_menu_path]['_children']);
        }

        return $ret;
    }
    
    public function systemGetAdminMenus()
    {
        return array(
            '/settings' => array(
                'label' => __('Sabai Settings', 'sabai'),
                'title' => __('Settings', 'sabai'),
            ),
        );
    }
    
    public function onFormBuildSystemAdminSettings(&$form)
    {
        if ($form['#name'] !== 'system_admin_settings') return;
        
        $token = $this->_application->Token('system_admin_settings', 1800, true);
        $form[$this->_name] = array(
            '#tree' => true,
            '#weight' => 1,
            'system' => array(
                '#class' => 'sabai-form-inline',
                '#title' => __('System Settings', 'sabai'),
                '#weight' => 1,
                'info' => array(
                    '#type' => 'item',
                    '#markup' => $this->_application->LinkToModal(
                        '<i class="fa fa-info-circle"></i> ' . __('System Information', 'sabai'),
                        $this->_application->Url('/info'),
                        array('width' => 600, 'no_escape' => true),
                        array('class' => 'sabai-btn sabai-btn-default sabai-btn-sm', 'data-modal-title' => __('System Information', 'sabai'))
                    ),
                ),
                'cache' => array(
                    '#type' => 'item',
                    '#markup' => $this->_application->LinkToRemote(
                        '<i class="fa fa-trash-o"></i> ' . __('Clear Cache', 'sabai'),
                        '#sabai-content',
                        $this->_application->Url('/settings/clearcache', array(Sabai_Request::PARAM_TOKEN => $token)),
                        array('width' => 600, 'no_escape' => true, 'post' => true, 'success' => 'trigger.after(" <span class=\"sabai-alert-success\"><i class=\"fa fa-check\"></i></span>");  return false;'),
                        array('class' => 'sabai-btn sabai-btn-default sabai-btn-sm')
                    ),
                ),
                'cron' => array(
                    '#type' => 'item',
                    '#markup' => $this->_application->LinkToModal(
                        '<i class="fa fa-refresh"></i> ' . __('Run Cron', 'sabai'),
                        $this->_application->Url('/cron'),
                        array('width' => 600, 'no_escape' => true),
                        array('class' => 'sabai-btn sabai-btn-default sabai-btn-sm', 'data-modal-title' => __('Run Cron', 'sabai'))
                    ),
                ),
            ),
            'addons' => $this->_getSystemAdminAddonsForm(),
        );
        $form['#submit'][0][] = array($this, 'submitSystemAdminSettingsForm');
    }

    public function submitSystemAdminSettingsForm($form)
    {
        $this->saveConfig($form->values[$this->_name] + $this->_config);
    }
    
    private function _getSystemAdminAddonsForm()
    {
        $form = array(
            '#title' => __('Sabai Add-Ons', 'sabai'),
            '#weight' => 3,
            'installed' => array(
                '#id' => 'sabai-system-admin-addons-installed',
                '#type' => 'tableselect',
                '#weight' => 2,
                '#header' => array('name' => __('Name', 'sabai'), 'description' => __('Description', 'sabai'), 'version' => __('Version', 'sabai'), 'links' => ''),
                '#multiple' => true,
                '#options' => array(),
            ),
            'installable' => array(
                '#id' => 'sabai-system-admin-addons-installable',
                '#type' => 'tableselect',
                '#weight' => 5,
                '#title' => __('Installable Add-ons', 'sabai'),
                '#header' => array('name' => __('Name', 'sabai'), 'description' => __('Description', 'sabai'), 'version' => __('Version', 'sabai'), 'links' => ''),
                '#multiple' => true,
                '#options' => array(),
                '#disabled' => true,
            ),
        );
        
        if (!isset($_GET['refresh']) || $_GET['refresh']) {
            $this->_application->reloadAddons();
        }
        $local_addons = $this->_application->getLocalAddons();
        
        // Fetch addons
        $addons = array();
        foreach ($this->getModel('Addon')->fetch(0, 0, 'name', 'ASC') as $addon) {
            if (!$this->_application->isAddonLoaded($addon->name)) continue;
            
            if ($addon->parent_addon && !$this->_application->isAddonLoaded($addon->parent_addon)) continue;
            
            $_addon = $this->_application->getAddon($addon->name);
            $info = $_addon->getAddonInfo();
            $info += array(
                'display_name' => $addon->parent_addon ? sprintf('%s (%s)', $addon->name, $addon->parent_addon) : $addon->name,
                'description' => null,
                'maintainers' => array(),
                'url' => null,
                'license' => null,
                'parent' => $addon->parent_addon,
                'children' => array(),
                'version' => $addon->version,
                'upgradeable' => $_addon->isUpgradeable($addon->version, $local_addons[$addon->name]['version']),
                'reloadable' => $_addon->isReloadable($addon->version, $local_addons[$addon->name]['version']),
                'settings_page' => $_addon instanceof Sabai_Addon_System_IAdminSettings ? true : $_addon->hasSettingsPage($addon->version),
                'uninstallable' => $_addon->isUninstallable($addon->version),
                'cloneable' => $_addon->isCloneable(),
            );
            foreach ($info['maintainers'] as $k => $_maintainer) {
                if (isset($_maintainer['active']) && $_maintainer['active'] != 'yes') {
                    unset($info['maintainers'][$k]);
                }
                $info['maintainers'][$k] = $_maintainer['name'];
            }
            if (isset($addons[$addon->name])) {
                $addons[$addon->name] += $info;
            } else {
                $addons[$addon->name] = $info;
            }
            if ($info['parent']) {
                if (isset($addons[$info['parent']])) {
                    $addons[$info['parent']]['children'][] = $addon->name;
                } else {
                    $addons[$info['parent']] = array('children' => array($addon->name));
                }
            }
        }
        ksort($addons);

        $has_upgradeable = $has_installable = array();
        
        foreach ($addons as $addon_name => $addon) {
            $links = array();
            if ($addon['upgradeable']) {
                $form['installed']['#row_attributes'][$addon_name]['@row']['class'] = 'sabai-alert sabai-alert-warning sabai-addon-' . strtolower($addon_name);
                $links[] = $this->_application->LinkToModal(
                    '',
                    '/addons/addon/' . $addon_name . '/upgrade',
                    array('width' => 600, 'icon' => 'level-up'),
                    array('title' => __('Upgrade this add-on', 'sabai'), 'data-modal-title' => sprintf(__('Upgrade %s Add-on', 'sabai'), $addon_name), 'class' => 'sabai-btn-warning')
                );
                $has_upgradeable[] = $addon_name;
            }
            if ($addon['uninstallable']) {
                $class = 'sabai-btn sabai-btn-danger';
                if (!empty($addon['children'])) {
                    $class .= ' sabai-disabled'; 
                }
                $links[] = $this->_application->LinkToModal(
                    '',
                    '/addons/addon/' . $addon_name . '/uninstall',
                    array('width' => 600, 'icon' => 'times'),
                    array('title' => __('Uninstall this add-on', 'sabai'), 'data-modal-title' => sprintf(__('Uninstall %s Add-on', 'sabai'), $addon_name), 'class' => $class)
                );
            }
            if ($addon['settings_page']) {
                if ($addon['settings_page'] === true) {
                    $links[] = $this->_application->LinkToModal(
                        '',
                        $this->_application->AdminUrl('/settings/' . strtolower($addon_name)),
                        array('icon' => 'cog', 'width' => 600),
                        array('title' => __('Configure this add-on', 'sabai'), 'data-modal-title' => sprintf(__('Configure %s Add-on', 'sabai'), $addon_name))
                    );
                } else {
                    $links[] = $this->_application->LinkTo(
                        '',
                        $this->_application->AdminUrl($addon['settings_page']),
                        array('icon' => 'cogs'),
                        array('title' => __('Configure this add-on', 'sabai'))
                    );
                }
            }
            if ($addon['cloneable']) {
                $links[] = $this->_application->LinkToModal(
                    '',
                    '/addons/addon/' . $addon_name . '/clone',
                    array('width' => 600, 'icon' => 'copy'),
                    array('title' => __('Clone this add-on', 'sabai'), 'data-modal-title' => sprintf(__('Clone %s Add-on', 'sabai'), $addon_name), 'class' => 'sabai-btn-primary')
                );
            }
            if (!$addon['upgradeable'] && $addon['reloadable']) {
                $links[] = $this->_application->LinkToModal(
                    '',
                    '/addons/addon/' . $addon_name . '/reload',
                    array('width' => 600, 'icon' => 'refresh'),
                    array('title' => __('Reload this add-on', 'sabai'), 'data-modal-title' => sprintf(__('Reload %s Add-on', 'sabai'), $addon_name), 'class' => 'sabai-btn-info')
                );
            }
            $form['installed']['#options'][$addon_name] = array(
                'name' => !empty($addon['maintainers'])
                    ? sprintf(__('<strong>%s</strong> by %s', 'sabai'), Sabai::h($addon['display_name']), Sabai::h(implode(', ', $addon['maintainers'])))
                    : '<strong>' . Sabai::h($addon['display_name']) . '</strong>',
                'description' => is_string($addon['description']) && strlen($addon['description'])
                    ? Sabai::h(mb_strimwidth(strip_tags(strtr($addon['description'], array("\r" => '', "\n" => ' '))), 0, 500, '...'))
                    : sprintf(__('%s addon for Sabai', 'sabai'), Sabai::h($addon['display_name'])),
                'version' => Sabai::h($addon['version']),
                'links' => $this->_application->ButtonLinks($links),
            );
            foreach (array_keys($form['installed']['#options'][$addon_name]) as $column_name) {
                $form['installed']['#row_attributes'][$addon_name][$column_name]['class'] = 'sabai-addon-column-' . $column_name;
            }
        }

        foreach ($local_addons as $addon_name => $addon_info) {
            // Skip if already installed
            if ($this->_application->isAddonLoaded($addon_name)) continue;
            
            $_addon = $this->_application->fetchAddon($addon_name);
            
            $class = 'sabai-btn-success';
            if (!$_addon->isInstallable()) {
                $class .= ' sabai-disabled';
                $form['installable']['#options_disabled'][] = $addon_name;
            } else {
                $has_installable = true;
            }
            
            $info = $_addon->getAddonInfo();
            $info += array(
                'display_name' => $_addon->hasParent() ? sprintf('%s (%s)', $addon_name, $_addon->hasParent()) : $addon_name,
                'description' => null,
                'maintainers' => array(),
                'url' => null,
                'license' => null,
            );     
            $maintainers = $links = array();
            foreach ($info['maintainers'] as $_maintainer) {
                if (isset($_maintainer['active']) && $_maintainer['active'] != 'yes') continue;
                $maintainers[] = $_maintainer['name'];
            }
            $links[] = $this->_application->LinkToModal(
                '',
                '/addons/installable/' . $addon_name . '/install',
                array('width' => 600, 'icon' => 'plus'),
                array('title' => __('Install this add-on', 'sabai'), 'data-modal-title' => sprintf(__('Install %s', 'sabai'), $addon_name), 'class' => $class)
            );
            if ($_addon->hasParent()) {
                $links[] = $this->_application->LinkToModal(
                    '',
                    '/addons/installable/' . $addon_name . '/delete',
                    array('width' => 600, 'icon' => 'times'),
                    array('title' => __('Delete this add-on', 'sabai'), 'data-modal-title' => sprintf(__('Delete %s', 'sabai'), $addon_name), 'class' => 'sabai-btn-danger')
                );
            }
            $form['installable']['#options'][$addon_name] = array(
                'name' => !empty($maintainers)
                    ? sprintf(__('<strong>%s</strong> by %s', 'sabai'), Sabai::h($info['display_name']), Sabai::h(implode(', ', $maintainers)))
                    : '<strong>' . Sabai::h($info['display_name']) . '</strong>',
                'description' => is_string($info['description']) && strlen($info['description'])
                    ? Sabai::h(mb_strimwidth(strip_tags(strtr($info['description'], array("\r" => '', "\n" => ' '))), 0, 500, '...'))
                    : sprintf(__('%s addon for Sabai', 'sabai'), Sabai::h($info['display_name'])),
                'version' => isset($addon_info['version']) ? $addon_info['version'] : __('Unknown', 'sabai'),
                'links' => $this->_application->ButtonLinks($links),
            );
            $form['installable']['#row_attributes'][$addon_name]['@row']['class'] = 'sabai-active';
            foreach (array_keys($form['installable']['#options'][$addon_name]) as $column_name) {
                $form['installable']['#row_attributes'][$addon_name][$column_name]['class'] = 'sabai-addon-column-' . $column_name;
            }
        }

        $token = $this->_application->Token('system_admin_addons');
        $send_data_js = 'data["addons"] = new Array(); jQuery("#sabai-system-admin-addons-installed input.sabai-form-check-target:checked").each(function(){data["addons"].push(jQuery(this).val());});';
        $form['bulk_actions'] = array(
            '#type' => 'markup',
            '#weight' => 3,
            '#markup' => $this->_application->LinkToRemote(
                $has_upgradeable ? _x('Upgrade Checked', 'Upgrade add-ons', 'sabai') : _x('Reload Checked', 'Reload add-ons', 'sabai'),
                '#sabai-content',
                $this->_application->Url('/addons/upgrade', array(Sabai_Request::PARAM_TOKEN => $token)),
                array('icon' => $has_upgradeable ? 'level-up' : 'refresh', 'post' => true, 'sendData' => $send_data_js),
                $has_upgradeable ? array('class' => 'sabai-system-bulk-action sabai-btn sabai-btn-sm sabai-btn-warning') : array('class' => 'sabai-system-bulk-action sabai-btn sabai-btn-sm sabai-btn-info')
            ),
        );
        
        return $form;
    }
    
    public function hasVarDir()
    {
        return array('clones');
    }
    
    public function systemGetAdminSettingsForm()
    {
        return array(
            'no_image' => array(
                '#type' => 'url',
                '#default_value' => @$this->_config['no_image'],
                '#title' => __('"No Image" image URL', 'sabai'),
                '#description' => sprintf(
                    __('Leave blank to use the system default (%s).', 'sabai'),
                    $this->_application->getPlatform()->getAssetsUrl() . '/images/no_image.png'
                ),
            ),
            'no_image_small' => array(
                '#type' => 'url',
                '#default_value' => @$this->_config['no_image_small'],
                '#title' => __('"No Image" image URL (small)', 'sabai'),
                '#description' => sprintf(
                    __('Leave blank to use the system default (%s).', 'sabai'),
                    $this->_application->getPlatform()->getAssetsUrl() . '/images/no_image_small.png'
                ),
            ),
            'no_ui_css' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($this->_config['no_ui_css']),
                '#title' => __('Do not load jQuery UI CSS', 'sabai'),
            ),
            'no_bxslider' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($this->_config['no_bxslider']),
                '#title' => __('Do not load bxslider javascript library on startup', 'sabai'),
            ),
        );
    }
}
