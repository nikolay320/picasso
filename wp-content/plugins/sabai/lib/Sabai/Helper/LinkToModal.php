<?php
class Sabai_Helper_LinkToModal extends Sabai_Helper
{
    public function help(Sabai $application, $linkText, $linkUrl, array $options = array(), array $attributes = array())
    {
        return $application->LinkToRemote($linkText, '#sabai-modal', $linkUrl, $options, $attributes);
    }
}