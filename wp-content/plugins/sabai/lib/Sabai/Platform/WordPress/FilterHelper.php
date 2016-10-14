<?php
class Sabai_Platform_WordPress_FilterHelper extends Sabai_Helper_Filter
{
    public function help(Sabai $application, $name, $value, array $args = array())
    {
        $value = parent::help($application, $name, $value, $args);
        array_unshift($args, 'sabai_' . $name, $value);
        return call_user_func_array('apply_filters', $args);
    }
}