<?php
class Sabai_Platform_WordPress_ActionHelper extends Sabai_Helper_Action
{
    public function help(Sabai $application, $name, array $args = array())
    {
        parent::help($application, $name, $args);
        do_action_ref_array('sabai_' . $name, $args);
    }
}