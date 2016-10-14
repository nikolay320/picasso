<?php
class Sabai_Addon_System_Helper_EmailSettings extends Sabai_Helper
{
    public function help(Sabai $application, $addonName, $name = null)
    {
        if (2 > $num_args = func_num_args()) return;
        
        if (!$settings = $application->getPlatform()->getOption($addonName . '_emails', false)) {
            return;
        }
        
        if ($num_args === 2) return $settings;
        if ($num_args === 3) return @$settings[$name];

        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        foreach ($args as $arg) {
            if (!isset($settings[$arg])) return null;

            $settings = $settings[$arg];
        }

        return $settings;
    }
}