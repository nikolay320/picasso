<?php
class Sabai_Helper_ControllerName extends Sabai_Helper
{
    public function help(Sabai $application, $controllerClass)
    {
        $parts = explode('_', $controllerClass);
        unset($parts[0], $parts[1], $parts[3]); // remove Sabai, Addon, Controller parts
        return strtolower(implode('_', $parts));
    }
}