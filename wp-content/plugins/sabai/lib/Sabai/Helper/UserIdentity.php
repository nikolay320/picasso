<?php
class Sabai_Helper_UserIdentity extends Sabai_Helper
{
    public function help(Sabai $application, $userId)
    {
        return $application->getPlatform()
            ->getUserIdentityFetcher()
            ->fetchById($userId);
    }
}