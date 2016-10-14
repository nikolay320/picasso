<?php
class Sabai_Addon_Directory_Helper_SendListingNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Entity_Entity $listing, $user = null, array $tags = array())
    {
        $bundle = $application->Entity_Bundle($listing);
        $tags += $application->Entity_TemplateTags($listing, 'listing_');
        if (!isset($user)) {
            $user = $application->Entity_Author($listing);
        } elseif ($user === false) {
            $user = null; // do not set author, for flagged notification
        }
        foreach ((array)$name as $notification_name) {
            $application->System_SendEmail($bundle->addon, 'listing_' . $notification_name, $tags, $user);
        }
    }
}