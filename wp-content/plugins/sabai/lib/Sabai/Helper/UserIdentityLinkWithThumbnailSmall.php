<?php
class Sabai_Helper_UserIdentityLinkWithThumbnailSmall extends Sabai_Helper
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
            return $application->UserIdentityLinkWithThumbnail($identity, $thumbnail_url, Sabai::THUMBNAIL_SIZE_SMALL, 5);
        }
    }
    
    private function _getThumbnailUrl(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        if ($identity->thumbnail_small) {
            return $identity->thumbnail_small;
        }
        if (!$identity->gravatar || !$identity->email) {
            return;
        }
        return $application->GravatarUrl($identity->email, Sabai::THUMBNAIL_SIZE_SMALL, $identity->gravatar_default, $identity->gravatar_rating);
    }
}