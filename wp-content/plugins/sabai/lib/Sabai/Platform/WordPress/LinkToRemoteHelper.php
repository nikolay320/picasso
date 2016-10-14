<?php
class Sabai_Platform_WordPress_LinkToRemoteHelper extends Sabai_Helper_LinkToRemote
{
    public function help(Sabai $application, $linkText, $update, $linkUrl, array $options = array(), array $attributes = array())
    {
        return parent::help($application, $linkText, $update, $linkUrl, $options, $attributes)->setUrl('#'); // disable non-ajax link
    }
}