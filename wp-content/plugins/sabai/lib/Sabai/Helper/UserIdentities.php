<?php
class Sabai_Helper_UserIdentities extends Sabai_Helper
{
    public function help(Sabai $application, array $userIds)
    {
        return $application->getPlatform()->getUserIdentityFetcher()->fetchByIds($userIds);
    }
}