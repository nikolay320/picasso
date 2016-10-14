<?php
class Sabai_Helper_IsAdministrator extends Sabai_Helper
{
    public function help(Sabai $application, SabaiFramework_User_Identity $identity = null)
    {
        if (!isset($identity)) {
            return $application->getUser()->isAdministrator();
        }
        return !$identity->isAnonymous() && $application->getPlatform()->isAdministrator($identity);
    }
}