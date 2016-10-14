<?php
class Sabai_Helper_LoadJquery extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        $application->LoadJs('//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js', 'jquery', null, false);
    }
}
