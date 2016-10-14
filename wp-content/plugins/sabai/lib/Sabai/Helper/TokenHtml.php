<?php
class Sabai_Helper_TokenHtml extends Sabai_Helper
{
    public function help(Sabai $application, $tokenId, $tokenLifetime = 1800, $reobtainable = false, $tokenName = Sabai_Request::PARAM_TOKEN)
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s" id="%s" />',
            Sabai::h($tokenName),
            $application->Token($tokenId, $tokenLifetime, $reobtainable),
            'sabai-' . strtolower(str_replace(array('_', ' '), '-', Sabai::h($tokenId))) . '-token'
        );
    }
}