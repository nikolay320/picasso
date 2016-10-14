<?php
class Sabai_Helper_User extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return $application->getUser();
    }
}