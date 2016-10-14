<?php
class Sabai_Helper_LoginUrl extends Sabai_Helper
{
    public function help(Sabai $application, $redirect)
    {
        return $application->getPlatform()->getLoginUrl((string)$redirect);
    }
}