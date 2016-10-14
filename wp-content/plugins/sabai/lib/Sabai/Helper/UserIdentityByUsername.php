<?php
class Sabai_Helper_UserIdentityByUsername extends Sabai_Helper
{
    /**
     * Gets a SabaiFramework_User_Identity by user name
     *
     * @return SabaiFramework_User_Identity
     * @param SabaiFrameworkApplication $application
     * @param string $username
     */
    public function help(Sabai $application, $username)
    {
        return $application->getPlatform()
            ->getUserIdentityFetcher()
            ->fetchByUsername($username);
    }
}