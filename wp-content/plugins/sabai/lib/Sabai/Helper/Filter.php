<?php
class Sabai_Helper_Filter extends Sabai_Helper
{
    public function help(Sabai $application, $name, $value, array $args = array())
    {
        if (is_object($value)) {
            array_unshift($args, $value);
        } else {
            // Pass in the value as reference so it can be altered
            $args = array_merge(array(&$value), $args);
        }
        // Dispatch filter event
        $application->getEventDispatcher()->dispatch(str_replace('_', '', $name) . 'filter', $args);

        return $value; // return the altered value
    }
}