<?php
class Sabai_Addon_System_Helper_PermissionCategories extends Sabai_Helper
{
    /**
     * Returns all available permission categories
     * @param Sabai $application
     */
    public function help(Sabai $application, array $categories = null, $useCache = true)
    {
        if (!$useCache
            || (!$permission_categories = $application->getPlatform()->getCache('system_permission_categories'))
        ) {
            $permission_categories = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_System_IPermissionCategories') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->systemGetPermissionCategories() as $category_name => $category_title) {
                    $permission_categories[$category_name] = array('title' => $category_title, 'permissions' => array());
                }
            }
            if (!empty($permission_categories)) {
                foreach ($application->getModel('Permission', 'System')->permissioncategoryName_in(array_keys($permission_categories))->fetch() as $permission) {
                    $permission_categories[$permission->permissioncategory_name]['permissions'][$permission->name] = array(
                        'title' => $permission->title,
                        'guest_allowed' => (bool)$permission->guest_allowed,
                    );
                }
            }
            $application->getPlatform()->setCache($permission_categories, 'system_permission_categories');
        }

        if (!isset($categories)) return $permission_categories;
        
        $ret = array();
        foreach ($categories as $category_name) {
            if (!isset($permission_categories[$category_name])) continue;
            
            $ret[$category_name] = $permission_categories[$category_name];
        }
        return $ret;
    }
}