<?php
class Sabai_Helper_LoadJson2 extends Sabai_Helper
{
    /**
     * Loads json2.js script file
     * @param Sabai $application
     */
    public function help(Sabai $application)
    {
        $application->LoadJs('json2.min.js', 'json2');
    }
}
