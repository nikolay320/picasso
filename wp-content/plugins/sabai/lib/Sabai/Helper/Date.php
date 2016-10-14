<?php
class Sabai_Helper_Date extends Sabai_Helper
{
    public function help(Sabai $application, $timestamp)
    {
        return date(__('F j, Y', 'sabai'), $timestamp);
    }
}