<?php
class Sabai_Addon_System_Helper_ClearCache extends Sabai_Helper
{
    public function help(Sabai $application, $log = null)
    {
        if (!isset($log)) $log = new ArrayObject();
        $application->getPlatform()->clearCache();
        $application->Action('system_clear_cache', array($log));
    }
}