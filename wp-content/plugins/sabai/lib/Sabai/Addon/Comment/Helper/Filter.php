<?php
class Sabai_Addon_Comment_Helper_Filter extends Sabai_Helper
{
    public function help(Sabai $application, $comment)
    {
        return strlen($comment) ? $application->Htmlize($comment, $application->getAddon('Comment')->getConfig('allow_blocks') ? false : true) : '';
    }   
}