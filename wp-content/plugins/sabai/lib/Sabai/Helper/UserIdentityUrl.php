<?php
class Sabai_Helper_UserIdentityUrl extends Sabai_Helper
{
    public function help(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        return $identity->url ? $identity->url : $application->getPlatform()->getHomeUrl();
    }
}