<?php
class Sabai_Helper_UserIdentityThumbnailLarge extends Sabai_Helper
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
        return $application->UserIdentityThumbnail($identity, $identity->thumbnail_large, Sabai::THUMBNAIL_SIZE_LARGE);
    }
}