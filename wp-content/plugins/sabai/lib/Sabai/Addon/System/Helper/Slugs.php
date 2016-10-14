<?php
class Sabai_Addon_System_Helper_Slugs extends Sabai_Helper
{
    /**
     * Returns all sluggable routes
     * @param Sabai $application
     */
    public function help(Sabai $application)
    {
        if (!$slugs = $application->getPlatform()->getCache('system_slugs')) {
            $slugs = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_System_ISlugs') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                if ($info = $application->getAddon($addon_name)->systemSlugsGetInfo()) {
                    if (!isset($slugs[$addon_name])) {
                        $slugs[$addon_name] = array('slugs' => array());
                    } 
                    $slugs[$addon_name] += $info;
                }
                
                foreach ($application->getAddon($addon_name)->systemGetSlugs() as $slug_name => $slug_info) {
                    $_addon_name = isset($slug_info['admin_addon']) ? $slug_info['admin_addon'] : $addon_name;
                    $slug = isset($slug_info['slug']) ? $slug_info['slug'] : $slug_name;
                    if (!empty($slug_info['parent'])) {
                        if (!isset($slugs[$_addon_name]['slugs'][$slug_info['parent']])) continue;
                        
                        $slug = $slugs[$_addon_name]['slugs'][$slug_info['parent']]['slug'] . '/' . $slug; 
                    }
                    
                    $slugs[$_addon_name]['slugs'][$slug_name] = array(
                        'slug' => $slug,
                        'title' => @$slug_info['title'],
                        'admin_title' => $slug_info['admin_title'],
                        'is_root' => !empty($slug_info['is_root']),
                        'is_required' => !empty($slug_info['is_required']),
                        'addon' => $addon_name,
                        'parent' => @$slug_info['parent'],
                    );
                }
            }
            $application->getPlatform()->setCache($slugs, 'system_slugs', 0);
        }

        return $slugs;
    }
}