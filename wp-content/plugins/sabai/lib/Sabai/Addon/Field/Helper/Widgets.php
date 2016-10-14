<?php
class Sabai_Addon_Field_Helper_Widgets extends Sabai_Helper
{
    /**
     * Returns all available field widgets
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$field_widgets = $application->getPlatform()->getCache('field_widgets'))
        ) {
            $field_widgets = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Field_IWidgets') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->fieldGetWidgetNames() as $widget_name) {
                    if (!$application->getAddon($addon_name)->fieldGetWidget($widget_name)) {
                        continue;
                    }
                    $field_widgets[$widget_name] = $addon_name;
                }
            }
            $application->getPlatform()->setCache($application->Filter('field_widgets', $field_widgets), 'field_widgets');
        }

        return $field_widgets;
    }
}