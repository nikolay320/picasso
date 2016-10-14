<?php
class Sabai_Helper_UserIdentityThumbnailSmall extends Sabai_Helper
{
    /**
     * Creates a thumbnail link to user
     *
     * @return string
     * @param SabaiFrameworkApplication $application
     * @param SabaiFramework_User_Identity $identity
     */
    public function help(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        return $application->UserIdentityThumbnail($identity, $identity->thumbnail_small, Sabai::THUMBNAIL_SIZE_SMALL);
    }
}