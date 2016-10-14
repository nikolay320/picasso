<?php
class Sabai_Helper_Time extends Sabai_Helper
{
    public function help(Sabai $application, $timestamp)
    {
        return date(__('g:i a', 'sabai'), $timestamp);
    }
}