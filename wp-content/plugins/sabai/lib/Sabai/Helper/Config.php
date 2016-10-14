<?php
class Sabai_Helper_Config extends Sabai_Helper
{
    public function help(Sabai $application, $addon = null)
    {
        if (!$addon instanceof Sabai_Addon) {
            $addon = $application->getAddon($addon);
        }
        if (2 >= $num_args = func_num_args()) {
            return $addon->getConfig();
        }
        $args = func_get_args();
        return call_user_func_array(array($addon, 'getConfig'), array_slice($args, 2));
    }
}