<?php
class Sabai_Helper_Camelize extends Sabai_Helper
{
    public function help(Sabai $application, $str)
    {
        return str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $str)));
    }
}