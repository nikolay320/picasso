<?php
interface Sabai_Addon_Widgets_IWidgets
{
    public function widgetsGetWidgetNames();
    public function widgetsGetWidget($widgetName);
}