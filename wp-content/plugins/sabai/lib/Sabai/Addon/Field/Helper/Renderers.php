<?php
class Sabai_Addon_Field_Helper_Renderers extends Sabai_Helper
{
    /**
     * Returns all available field renderers
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$renderers = $application->getPlatform()->getCache('field_renderers'))
        ) {
            $renderers = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Field_IRenderers') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->fieldGetRendererNames() as $renderer_name) {
                    if (!$application->getAddon($addon_name)->fieldGetRenderer($renderer_name)) {
                        continue;
                    }
                    $renderers[$renderer_name] = $addon_name;
                }
            }
            $application->getPlatform()->setCache($application->Filter('field_renderers', $renderers), 'field_renderers', 0);
        }

        return $renderers;
    }
}