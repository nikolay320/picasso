<?php
class Sabai_Addon_Widgets_Helper_Widgets extends Sabai_Helper
{
    /**
     * Returns all available widgets
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$widgets_widgets = $application->getPlatform()->getCache('widgets_widgets'))
        ) {
            $widgets_widgets = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Widgets_IWidgets') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->widgetsGetWidgetNames() as $widget_name) {
                    if (!$widget = $application->getAddon($addon_name)->widgetsGetWidget($widget_name)) {
                        continue;
                    }
                    $widgets_widgets[$widget_name] = array(
                        'addon' => $addon_name,
                        'title' => $widget->widgetsWidgetGetTitle(),
                        'summary' => $widget->widgetsWidgetGetSummary(),
                    );
                }
            }
            $application->getPlatform()->setCache($widgets_widgets, 'widgets_widgets', 0);
        }

        return $widgets_widgets;
    }
}