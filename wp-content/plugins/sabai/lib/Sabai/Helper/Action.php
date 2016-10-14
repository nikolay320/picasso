<?php
class Sabai_Helper_Action extends Sabai_Helper
{
    public function help(Sabai $application, $name, array $args = array())
    {
        $application->getEventDispatcher()->dispatch(str_replace('_', '', $name), $args);
    }
}