<?php
class Sabai_Addon_Directory_Helper_ClaimListing extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $listing, SabaiFramework_User_Identity $identity, $duration = 0, array $values = array())
    {
        $application->Entity_LoadFields($listing);
        if (($current_claim = $listing->getSingleFieldValue('directory_claim'))
            && $current_claim['claimed_by'] == $identity->id
            && $current_claim['expires_at'] > time()
        ) {
            $claimed_at = $current_claim['claimed_at'];
            $expires_at = empty($duration) ? 0 : $current_claim['expires_at'] + $duration * 86400; // extend expiration time
        } else {
            $claimed_at = time();
            $expires_at = empty($duration) ? 0 : time() + $duration * 86400;
        }
        $values['directory_claim'] = array(
            'claimed_by' => $identity->id,
            'claimed_at' => $claimed_at,
            'expires_at' => $expires_at,
        );
        // Change author to owner if the current author is an anonymous user
        if (!$listing->getAuthorId()) {
            $values['content_guest_author'] = false;
            $values['content_post_user_id'] = $identity->id;
        }
        $application->Entity_Save($listing, $values);
    }
}