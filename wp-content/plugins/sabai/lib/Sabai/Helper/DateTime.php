<?php
class Sabai_Helper_DateTime extends Sabai_Helper
{
    public function help(Sabai $application, $timestamp)
    {
        return date(__('F j, Y g:i a', 'sabai'), $timestamp);
    }
}