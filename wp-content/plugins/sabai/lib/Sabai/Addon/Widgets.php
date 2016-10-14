<?php
class Sabai_Addon_Widgets extends Sabai_Addon
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';

    public function onWidgetsIWidgetsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('widgets_widgets');
    }

    public function onWidgetsIWidgetsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('widgets_widgets');
    }

    public function onWidgetsIWidgetsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('widgets_widgets');
    }
}