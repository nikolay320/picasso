<?php
interface Sabai_Addon_Widgets_IWidget
{
    public function widgetsWidgetGetTitle();
    public function widgetsWidgetGetSummary();
    public function widgetsWidgetGetSettings();
    public function widgetsWidgetGetLabel();
    public function widgetsWidgetGetContent(array $settings);
    public function widgetsWidgetOnSettingsSaved(array $settings, array $oldSettings);
}