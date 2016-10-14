<?php
class Sabai_Addon_DirectoryCSVImport extends Sabai_Addon
    implements Sabai_Addon_System_IAdminRouter
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-directory';
    
    public function isUninstallable($currentVersion)
    {
        return true;
    }

    public function systemGetAdminRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->type_is('directory_listing')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $routes[$bundle->getAdminPath() . '/import'] = array(
                'controller' => 'Import',
                'title_callback' => true,
                'callback_path' => 'import',
                'controller_addon' => $this->_name,
                'priority' => 5,
            );
            //$routes[$bundle->getAdminPath() . '/export'] = array(
            //    'controller' => 'Export',
            //    'title_callback' => true,
            //    'callback_path' => 'export',
            //    'controller_addon' => $this->_name,
            //    'priority' => 5,
            //);
            //$routes[$bundle->getAdminPath() . '/export_ajax'] = array(
            //    'controller' => 'ExportAjax',
            //    'type' => Sabai::ROUTE_CALLBACK,
            //    'controller_addon' => $this->_name,
            //    'priority' => 5,
            //);
        }
        foreach ($this->_application->getModel('Bundle', 'Entity')->type_is('directory_category')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $routes[$bundle->getAdminPath() . '/import'] = array(
                'controller' => 'ImportCategories',
                'title_callback' => true,
                'callback_path' => 'import',
                'controller_addon' => $this->_name,
                'priority' => 5,
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
            case 'import':
                return __('Import CSV', 'sabai-directory');
            //case 'export':
            //    return 'Export CSV';
        }
    }
    
    public function onDirectoryInstallSuccess($addon)
    {        
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
  
    public function onDirectoryUninstallSuccess($addon)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onDirectoryUpgradeSuccess(Sabai_Addon $addon, $log, $previousVersion)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
  
    public function onContentAdminPostsLinksFilter(&$links, $bundle)
    {
        if ($bundle->type !== 'directory_listing') return;
        
        $this->_addCsvLinks($links, $bundle);
    }
    
    public function onTaxonomyAdminTermsLinksFilter(&$links, $bundle)
    {
        if ($bundle->type !== 'directory_category') return;
        
        $this->_addCsvLinks($links, $bundle);
    }
    
    protected function _addCsvLinks(&$links, $bundle)
    {
        foreach (array('import' => __('Import CSV', 'sabai-directory'), /*'export' => __('Export CSV', 'sabai-directory')*/) as $key => $label) {
            $links[] = $this->_application->LinkTo(
                $label,
                $this->_application->Url($bundle->getAdminPath() . '/' . $key),
                array('icon' => 'table'),
                array('class' => 'sabai-btn sabai-btn-primary sabai-btn-sm')
            );
        }
    }
}