<?php
class Sabai_Addon_FieldUI extends Sabai_Addon
    implements Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';

    public function systemGetAdminRoutes()
    {
        $routes = array();
        
        foreach ($this->_application->getModel('Bundle', 'Entity')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            if (isset($bundle->info['fieldui_enable']) && $bundle->info['fieldui_enable'] === false) {
                continue;
            }
            $routes[$bundle->getAdminPath() . '/fields'] = array(
                'controller' => 'Fields',
                'type' => Sabai::ROUTE_TAB,
                'title_callback' => true,
                'weight' => 10,
                'callback_path' => 'fields'
            );
            if (!empty($bundle->info['fieldui_enable_top'])) {
                $routes[$bundle->getAdminPath() . '/fields']['data'] = array('clear_tabs' => $bundle->info['fieldui_enable_top']);
            }
            if (!empty($bundle->info['filterable'])) {
                $routes[$bundle->getAdminPath() . '/fields/filter'] = array(
                    'controller' => 'FilterFields',
                    'type' => Sabai::ROUTE_TAB,
                    'title_callback' => true,
                    'weight' => 10,
                    'callback_path' => 'filter_fields'
                );
            }
            $routes[$bundle->getAdminPath() . '/fields/submit'] = array(
                'controller' => 'SubmitFields',
                'type' => Sabai::ROUTE_CALLBACK,
                'method' => 'post',
            );
            $routes[$bundle->getAdminPath() . '/fields/create'] = array(
                'controller' => 'CreateField',
            );
            $routes[$bundle->getAdminPath() . '/fields/edit'] = array(
                'controller' => 'EditField',
            );
            $routes[$bundle->getAdminPath() . '/fields/edit_widget'] = array(
                'controller' => 'EditFieldWidget',
            );
            $routes[$bundle->getAdminPath() . '/fields/filter/submit'] = array(
                'controller' => 'SubmitFilterFields',
                'type' => Sabai::ROUTE_CALLBACK,
                'method' => 'post',
            );
            $routes[$bundle->getAdminPath() . '/fields/filter/create'] = array(
                'controller' => 'CreateFilterField',
            );
            $routes[$bundle->getAdminPath() . '/fields/filter/edit'] = array(
                'controller' => 'EditFilterField',
            );
            $routes[$bundle->getAdminPath() . '/fields/filter/edit_filter'] = array(
                'controller' => 'EditFilterFieldFilter',
            );
        }

        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {

    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'fields':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? _x('Manage Form', 'sabai') : _x('Fields', 'admin-tab', 'sabai');
            case 'filter_fields':
                return _x('Manage Filters', 'sabai');
        }
    }
    
    public function onSabaiWebResponseRenderFieldUIAdminFields($context, $response, $template)
    {        
        $submit_confirm = __('One or more fields have not been saved. You must save or delete these fields first before submitting the form.', 'sabai');
        $leave_confirm = __('You have made changes but it has not been saved. You must submit the form for the changes to be saved permanently.', 'sabai');
        $delete_confirm = __('Are you sure?', 'sabai');
        $this->_application->getPlatform()->addJs(sprintf('SABAI.FieldUI.adminFields({submitConfirm:"%s", leaveConfirm:"%s", deleteFieldConfirm: "%s"});', $submit_confirm, $leave_confirm, $delete_confirm), 'fieldui-init');
        $this->_application->LoadJqueryUi(array('sortable'));
        $this->_application->LoadJs('sabai-fieldui-admin-fields.min.js', 'sabai-fieldui-admin-fields', array('sabai', 'jquery-ui-sortable'));
        $this->_application->LoadCss('sabai-fieldui-admin-fields.min.css', 'sabai-fieldui-admin-fields');
        if ($this->_application->getPlatform()->isLanguageRTL()) {
            $this->_application->LoadCss('sabai-fieldui-admin-fields-rtl.min.css', 'sabai-fieldui-admin-fields-rtl', 'sabai-fieldui-admin-fields');
        }
    }
    
    public function onSabaiWebResponseRenderFieldUIAdminFilterFields($context, $response, $template)
    {        
        $submit_confirm = __('One or more fields have not been saved. You must save or delete these fields first before submitting the form.', 'sabai');
        $leave_confirm = __('You have made changes but it has not been saved. You must submit the form for the changes to be saved permanently.', 'sabai');
        $delete_confirm = __('Are you sure?', 'sabai');
        $this->_application->getPlatform()->addJs(sprintf('SABAI.FieldUI.adminFields({submitConfirm:"%s", leaveConfirm:"%s", deleteFieldConfirm: "%s"});', $submit_confirm, $leave_confirm, $delete_confirm), 'fieldui-init');
        $this->_application->LoadJqueryUi(array('sortable'));
        $this->_application->LoadJs('sabai-fieldui-admin-filter-fields.min.js', 'sabai-fieldui-admin-filter-fields', array('sabai', 'jquery-ui-sortable'));
        $this->_application->LoadCss('sabai-fieldui-admin-filter-fields.min.css', 'sabai-fieldui-admin-filter-fields');
        if ($this->_application->getPlatform()->isLanguageRTL()) {
            $this->_application->LoadCss('sabai-fieldui-admin-filter-fields-rtl.min.css', 'sabai-fieldui-admin-filter-fields-rtl');
        }
    }
    
    public function onFieldUIAdminFields()
    {
        $this->_application->LoadJs('typeahead.bundle.min.js', 'twitter-typeahead', 'jquery');
        $this->_application->Date_Scripts();
    }
    
    public function onEntityITypesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }

    public function onEntityITypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }

    public function onEntityITypesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {  
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
}