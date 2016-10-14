<?php
class Sabai_Addon_Directory_Helper_SendClaimNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Directory_Model_Claim $claim)
    {
        if (!$listing = @$claim->fetchObject('Entity')) {
            $claim->with('Entity'); // load listing entity associated with the claim
            $listing = $claim->Entity;
        }
        if (!$listing->isPublished()) {
            // Send notification if the listing is published. Otherwise, listing_approved notification should be used to notify users
            return;
        }
        $tags = array(
            '{claim_id}' => $claim->id,
            '{claim_comment}' => $claim->comment,
            '{claim_user_name}' => $claim->User->name,
            '{claim_user_email}' => $claim->User->email,
            '{claim_date}' => $application->Date($claim->created),
            '{claim_admin_note}' => $claim->admin_note,
            '{claim_admin_url}' => $application->AdminUrl('/' . strtolower($application->Entity_Addon($listing)->getName()) . '/claims/' . $claim->id, array(), '', '&'), 
        );
        $tags += $application->Entity_TemplateTags($listing, 'listing_');
        foreach ((array)$name as $notification_name) {
            $application->System_SendEmail($application->Entity_Bundle($listing)->addon, 'claim_' . $notification_name, $tags, $claim->User);
        }
    }
}