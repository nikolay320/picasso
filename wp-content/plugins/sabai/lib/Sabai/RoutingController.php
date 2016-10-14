<?php
abstract class Sabai_RoutingController extends SabaiFramework_Application_RoutingController
{
    private $_accessCallbackResults = array(), $_tabsAdded = 0;
    protected $_application, $_parent;

    public function __construct()
    {
        parent::__construct(true);
    }

    /* Start implementation of SabaiFramework_Application_Controller */

    public function setApplication(SabaiFramework_Application $application)
    {
        $this->_application = $application;

        return $this;
    }

    public function getApplication()
    {
        return $this->_application;
    }

    public function setParent(SabaiFramework_Application_RoutingController $controller)
    {
        $this->_parent = $controller;

        return $this;
    }

    /* End implementation of SabaiFramework_Application_Controller */

    final public function __call($method, $args)
    {
        return call_user_func_array(array($this->_application, $method), $args);
    }

    protected function _isRoutable(SabaiFramework_Application_Context $context, $route)
    {
        $context->setContentType('html');
        if ($dot_pos = strpos($route, '.')) {
            $content_type = substr($route, $dot_pos + 1);
            $route = substr($route, 0, $dot_pos);
        } elseif ($context->getRequest()->has(Sabai_Request::PARAM_CONTENT_TYPE)) {
            $content_type = $context->getRequest()->asStr(Sabai_Request::PARAM_CONTENT_TYPE);
        }
        if (isset($content_type)
            && in_array($content_type, array('xml', 'json'))
        ) {
            $context->setContentType($content_type);
        }

        $paths_requested = explode('/', trim($route, '/') . '/');

        if (!$requested_root_route = array_shift($paths_requested)) return false;

        $requested_root_path = '/' . $requested_root_route;

        if ((!$all_routes = $this->_getAddonRoutes($requested_root_path))
            || (!$route_matched = @$all_routes[$requested_root_path])
            || !$this->isAddonLoaded($route_matched['addon'])
        ) {
            $context->setNotFoundError();

            return false;
        }

        $this->setCurrentAddon($this->getAddon($route_matched['addon'])->hasParent() || !isset($route_matched['controller_addon']) ? $route_matched['addon'] : $route_matched['controller_addon']);

        // Check if can access
        if (!$this->_canAccessRoute($context, $route_matched)) {
            // Access denied. Set error if not already set
            if (!$context->isError()) $context->setForbiddenError();

            return false;
        }

        // Set page info of the root route
        if ($route_matched_title = $this->_getTitle($context, $route_matched)) {
            $context->setInfo($route_matched_title, $this->Url($requested_root_path));
        }

        // Initialize some required variables
        $path_selected = $requested_root_route;
        $paths_matched = $paths_selected = array($requested_root_route);
        $tabs = $menus = $inline_tabs = $dynamic_route_keys = array();
        $last_route_matched = $route_matched;

        while (($routes = @$route_matched['routes'])
            && null !== ($path_requested = array_shift($paths_requested))
        ) {
            foreach ($routes as $route_key => $route_path) {
                if (!isset($all_routes[$route_path])) continue;

                $route_data = $all_routes[$route_path];

                if ($route_data['type'] == Sabai::ROUTE_TAB) {
                    // Dynamic routes may not become tabs
                    if (0 === strpos($route_key, ':')) continue;

                    // Is the current user allowed to access the link to this route?
                    if (!$this->_canAccessRoute($context, $route_data, Sabai::ROUTE_ACCESS_LINK)) {
                        continue;
                    }

                    // Add tab
                    if ($title = $this->_getTitle($context, $route_data, Sabai::ROUTE_TITLE_TAB)) {
                        $tabs[$route_data['weight'] + 1][$route_key] = array(
                            'title' => $title,
                            'url' => '/' . implode('/', $paths_matched) . '/' . $route_key,
                            'ajax' => $route_data['ajax'],
                            'class' => $route_data['class'],
                            'addon' => $route_data['addon'],
                            'featured' => !empty($route_data['data']['featured']),
                            'disabled' => !empty($route_data['data']['disabled']),
                            'options' => isset($route_data['data']['link_options']) ? $route_data['data']['link_options'] : array(),
                        );
                    } else {
                        // Convert this route to a non-tab route because no tab title
                        $all_routes[$route_data['path']]['type'] = Sabai::ROUTE_NORMAL;
                    }
                } elseif ($route_data['type'] == Sabai::ROUTE_MENU) {
                    // Dynamic routes may not become menu items
                    if (0 === strpos($route_key, ':')) continue;

                    $menus[$route_key] = $route_data;
                } elseif ($route_data['type'] == Sabai::ROUTE_INLINE_TAB) {
                    // Dynamic routes may not become inline tabs
                    if (0 === strpos($route_key, ':')) continue;

                    $inline_tabs[$route_key] = $route_data;
                }
            }

            if (isset($route_matched['controller'])) {
                $tabs[0][''] = array(
                    'title' => ($tab_title = $this->_getTitle($context, $route_matched, Sabai::ROUTE_TITLE_TAB_DEFAULT)) ? $tab_title : __('Top', 'sabai'),
                    'url' => '/' .  implode('/', $paths_matched),
                    'ajax' => false, // force no ajax for the default tab
                    'class' => isset($route_matched['class']) ? $route_matched['class'] : null,
                    'addon' => $route_matched['addon'],
                    '_route' => $route_matched, // for internal use
                    'options' => isset($route_matched['data']['link_options']) ? $route_matched['data']['link_options'] : array(),
                    'clear_tabs' => isset($route_matched['data']['clear_tabs']) ? $route_matched['data']['clear_tabs'] : null,
                );
            }

            // Some access callbacks set the response status as error, but the status should not be changed here since
            // we are now just checking whether or not the route is accessible, not really trying to access the route.
            $context->setView();

            // Default route
            if ($path_requested == '') {
                if ($this->_addTabs($context, $tabs)) {
                    $context->setCurrentTab('')
                        ->setInfo($this->_getTitle($context, $tabs[0]['']['_route'], Sabai::ROUTE_TITLE_TAB_DEFAULT), $this->Url($tabs[0]['']['url']));
                }
                $this->_addMenus($context, $menus, $paths_matched);
                $this->_setInlineTabs($context, $inline_tabs, $paths_matched);

                break;
            }

            if (isset($routes[$path_requested])) {
                $path_selected = $path_requested;
                $route_matched = $all_routes[$routes[$path_requested]];
            } else {
                $matched = false;
                // Check if dynamic routes are defined and any matching route
                krsort($routes, SORT_STRING);
                foreach ($routes as $route_key => $route_path) {
                    if (0 !== strpos($route_key, ':')) continue;
                    if (!isset($all_routes[$route_path])) continue;

                    $route_data = $all_routes[$route_path];

                    if (!empty($route_data['format'][$route_key])) {
                        $regex = '(' . str_replace('#', '\#', $route_data['format'][$route_key]) . ')';
                    } else {
                        $regex = '([a-z0-9~\s\.:_\-]+)';
                    }
                    if (!preg_match('#^' . $regex . '#i', $path_requested, $matches)) continue;

                    $context->getRequest()->set(substr($route_key, 1), $matches[0]);
                    $dynamic_route_keys[$route_key] = $matches[0];
                    $path_selected = $route_key;
                    $route_matched = $route_data;
                    $matched = true;
                    break;
                }

                if (!$matched) {
                    $context->setNotFoundError();
                    
                    return false;
                }
            }

            // Is the current user allowed to access the content of this route?
            if (!$this->_canAccessRoute($context, $route_matched)) {
                // Access denied. Set error if not already set
                if (!$context->isError()) $context->setForbiddenError();

                return false;
            }

            if ($route_matched['type'] != Sabai::ROUTE_CALLBACK) {
                $breadcrumbs = array();

                if ($route_matched['type'] != Sabai::ROUTE_INLINE_TAB
                    && ($title = $this->_getTitle($context, $route_matched))
                ) {
                    $breadcrumbs[] = array(
                        'title' => $title,
                        'url' => '/' . implode('/', $paths_matched) . '/' . $path_requested,
                    );
                }

                if ($this->_addTabs($context, $tabs)) {
                    $current_tab = $path_selected;
                    // Resolve current tab if requested route is not a tab
                    if ($route_matched['type'] != Sabai::ROUTE_TAB) {
                        // Set the default tab as the current tab
                        $current_tab = '';

                        // Add breadcrumb for the default tab
                        $breadcrumbs[] = array(
                            'title' => ($title = $this->_getTitle($context, $tabs[0]['']['_route'])) ? $title : __('Top', 'sabai'),
                            'url' => $tabs[0]['']['url'],
                        );
                    }
                    $context->setCurrentTab($current_tab);
                }

                foreach (array_reverse($breadcrumbs) as $bc) {
                    $context->setInfo($bc['title'], $this->Url($bc['url']));
                }
            }

            $paths_matched[] = $path_requested;
            $paths_selected[] = $path_selected;

            if (!empty($route_matched['controller']) || !empty($route_matched['forward'])) {
                $last_route_matched = $route_matched;
            }

            // Clear menus/tabs
            $tabs = $menus = $inline_tabs = array();
        }

        // Any valid route has matched?
        if (empty($last_route_matched)) {
            $context->setNotFoundError();

            return false;
        }

        // We don't need routes data anymore, save memory
        unset($last_route_matched['routes']);
        
        $this->setCurrentAddon($this->getAddon($last_route_matched['addon'])->hasParent()
            ? $last_route_matched['addon']
            : $last_route_matched['controller_addon']
        );

        if (!empty($last_route_matched['method'])
            && strcasecmp(Sabai_Request::method(), $last_route_matched['method']) !== 0
        ) {
            // The requested method is not allowed for this route
            $context->setMethodNotAllowedError();

            return false;
        }

        if (!empty($last_route_matched['forward'])) {
            // Convert dynamic route parts to actual values
            $last_route_matched['forward'] = strtr($last_route_matched['forward'], $dynamic_route_keys);
        } else {
            if (@$last_route_matched['type'] == Sabai::ROUTE_INLINE_TAB
                && !$context->getRequest()->isPostMethod()
                && $context->getContainer() === '#sabai-content'
            ) {
                // Inline tab content may not be accessed directly unless the request is POST or ajax.
                // Redirect to the parent route with the tab of requested inline content selected.
                $route_matched = '/' . implode('/', $paths_matched);
                $tab = basename($route_matched);
                $params = array(Sabai_Request::$inlineTabParam => $tab) + $context->getRequest()->getParams();
                // Remove dynamic route params since they are included in the path
                foreach (array_keys($dynamic_route_keys) as $dynamic_route_key) {
                    unset($params[substr($dynamic_route_key, 1)]);
                }
                // Pass on fragment to the redirected URL if any
                $fragment = empty($params['__fragment']) ? 'sabai-inline-content-' . $tab : $params['__fragment'];
                // Remove route and fragment params
                unset($params[$this->getRouteParam()], $params['__fragment']);
                // Redirect
                $context->setRedirect($this->Url(/*parent route*/dirname($route_matched), $params, $fragment));

                return false;
            }
            
            if (empty($last_route_matched['controller'])) {
                $context->setRedirect($this->_application->getPlatform()->getHomeUrl());
                
                return false;
            }

            $this->_processRouteData($last_route_matched);
        }

        $GLOBALS['sabai_route'] = '/' . implode('/', $paths_matched);
        return new Sabai_Route($GLOBALS['sabai_route'] . '/', $last_route_matched);
    }

    abstract protected function _getAddonRoutes($rootPath);
    abstract protected function _processAccessCallback(Sabai_Context $context, array &$routeData, $accessType);
    abstract protected function _processTitleCallback(Sabai_Context $context, array $routeData, $titleType);
    abstract protected function _processRouteData(array &$routeData);

    private function _canAccessRoute(Sabai_Context $context, &$route, $accessType = Sabai::ROUTE_ACCESS_CONTENT)
    {
        if (isset($route['callback_addon']) && !$this->isAddonLoaded($route['callback_addon'])) return false;
        
        if (empty($route['access_callback'])) return true;

        // Make sure the callback is called only once for each path
        $path = $route['path'];
        if (!isset($this->_accessCallbackResults[$path][$accessType])) {
            $this->_accessCallbackResults[$path][$accessType] = $this->_processAccessCallback($context, $route, $accessType);
            // Also deny access if any error is set in context
            if ($context->isError()) {
                $this->_accessCallbackResults[$path][$accessType] = false;
            }
        }

        return $this->_accessCallbackResults[$path][$accessType];
    }

    private function _getTitle(Sabai_Context $context, array $routeData, $titleType = Sabai::ROUTE_TITLE_NORMAL)
    {
        if (empty($routeData['title_callback'])) return @$routeData['title'];

        return $this->_processTitleCallback($context, $routeData, $titleType);
    }

    private function _addTabs(Sabai_Context $context, array $tabs)
    {
        ksort($tabs);
        $_tabs = array();
        foreach (array_keys($tabs) as $weight) {
            foreach ($tabs[$weight] as $tab_name => $tab_data) {
                $_tabs[$tab_name] = $tab_data;
            }
        }

        if (count($_tabs) <= 1 && empty($tabs[0]['']['clear_tabs'])) return false; // do not show sub tabs

        // Clear already added tags to prevent nested tags
        if (!empty($tabs[0]['']['clear_tabs'])) {
            if ($this->_tabsAdded) {
                $context->clearTabs();
            }
            if (is_string($tabs[0]['']['clear_tabs'])) {
                $context->setTitle($tabs[0]['']['clear_tabs']);
            }
        }
        
        // Add tabs
        $context->pushTabs($_tabs);
        ++$this->_tabsAdded;

        return true;
    }

    private function _addMenus(Sabai_Context $context, array $menus, $pathsMatched)
    {
        $_menus = array();
        foreach ($menus as $route_key => $route_data) {
            // Is the current user allowed to access the link to this route?
            if (!$this->_canAccessRoute($context, $route_data, Sabai::ROUTE_ACCESS_LINK)) continue;

            if (!$title = $this->_getTitle($context, $route_data, Sabai::ROUTE_TITLE_MENU)) continue;

            // Add menu
            $_menus[$route_data['weight']][$route_key] = array(
                'title' => $title,
                'url' => '/' . implode('/', $pathsMatched) . '/' . $route_key,
                'ajax' => $route_data['ajax'],
                'class' => $route_data['class'],
                'addon' => $route_data['addon'],
                'options' => isset($route_data['data']['link_options']) ? $route_data['data']['link_options'] : array(),
            );
        }
        if (empty($_menus)) return;

        ksort($_menus);
        $menus = array();
        foreach (array_keys($_menus) as $weight) {
            foreach ($_menus[$weight] as $data) {
                $menus[] = $data;
            }
        }
        $context->setMenus($menus);
    }

    private function _setInlineTabs(Sabai_Context $context, array $inlineTabs, $pathsMatched)
    {
        $inline_tabs = array();
        foreach ($inlineTabs as $route_key => $route_data) {
            // Must have a controller defined and not a forwarding route
            if (empty($route_data['controller']) || !empty($route_data['forward'])) continue;

            // Is the current user allowed to access the link to this route?
            if (!$this->_canAccessRoute($context, $route_data, Sabai::ROUTE_ACCESS_LINK)) continue;

            unset($route_data['routes']);
            $this->_processRouteData($route_data);
            
            // Add inline content
            if ($title = $this->_getTitle($context, $route_data, Sabai::ROUTE_TITLE_TAB)) {
                $inline_tabs[$route_data['weight']][$route_key] = array(
                    'title' => $title,
                    'url' => array(
                        'route' => '/' . implode('/', $pathsMatched) . '/' . $route_key . '/',
                        'params' => array('__fragment' => 'sabai-inline-nav') // pass fragment as param to avoid js conflict
                    ),
                    'class' => $route_data['class'],
                    'addon' => $route_data['addon'],
                    'route' => array('/' . implode('/', $pathsMatched) . '/' . $route_key . '/', $route_data),
                    'featured' => !empty($route_data['data']['featured']),
                    'disabled' => !empty($route_data['data']['disabled']),
                    'options' => isset($route_data['data']['link_options']) ? $route_data['data']['link_options'] : array(),
                    'hide_empty' => !empty($route_data['data']['hide_empty']),
                );
            }
        }
        if (empty($inline_tabs)) return;

        ksort($inline_tabs);
        $_tabs = array();
        foreach (array_keys($inline_tabs) as $weight) {
            foreach ($inline_tabs[$weight] as $path => $data) {
                $_tabs[$path] = $data;
            }
        }
        $context->setInlineTabs($_tabs);
    }
}
