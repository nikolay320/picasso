<?php
class Sabai_Helper_UserIdentityThumbnailMedium extends Sabai_Helper
{
    /**
     * Creates a thumbnail link to user
     *
     * @return string
     * @param Sabai $application
     * @param SabaiFramework_User_Identity $identity
     */
    public function help(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        return $application->UserIdentityThumbnail($identity, $identity->thumbnail_medium, Sabai::THUMBNAIL_SIZE_MEDIUM);
    }
}