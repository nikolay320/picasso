<?php
class Sabai_Helper_UserIdentityByEmail extends Sabai_Helper
{
    /**
     * Gets a SabaiFramework_User_Identity by email address
     *
     * @return SabaiFramework_User_Identity
     * @param SabaiFrameworkApplication $application
     * @param string $email
     */
    public function help(Sabai $application, $email)
    {
        return $application->getPlatform()
            ->getUserIdentityFetcher()
            ->fetchByEmail($email);
    }
}