<?php
class Sabai_Addon_Directory_Helper_SendPhotoNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Entity_Entity $photo, $user = null, array $tags = array())
    {
        if (!$listing = $application->Content_ParentPost($photo)) {
            return;
        }
        $bundle = $application->Entity_Bundle($photo);
        $tags += $application->Entity_TemplateTags($photo, 'photo_') + $application->Entity_TemplateTags($listing, 'listing_');
        if (!isset($user)) {
            $user = $application->Entity_Author($photo);
        } elseif ($user === false) {
            $user = null; // do not set author, for flagged notification
        }
        foreach ((array)$name as $notification_name) {
            $application->System_SendEmail($bundle->addon, 'photo_' . $notification_name, $tags, $user);
        }
    }
}