<?php
class Sabai_Helper_UserIdentityLinkWithThumbnailMedium extends Sabai_Helper
{
    /**
     * Creates an HTML link of a user
     *
     * @return string
     * @param SabaiFrameworkApplication $application
     * @param SabaiFramework_User_Identity $identity
     */
    public function help(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        if (!$thumbnail_url = $this->_getThumbnailUrl($application, $identity)) {
            return $application->UserIdentityLink($identity);
        } else {
            return $application->UserIdentityLinkWithThumbnail($identity, $thumbnail_url, Sabai::THUMBNAIL_SIZE_MEDIUM, 10);
        }
    }
    
    private function _getThumbnailUrl(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        if ($identity->thumbnail_medium) {
            return $identity->thumbnail_medium;
        }
        if (!$identity->gravatar || !$identity->email) {
            return;
        }
        return $application->GravatarUrl($identity->email, Sabai::THUMBNAIL_SIZE_MEDIUM, $identity->gravatar_default, $identity->gravatar_rating);
    }
}