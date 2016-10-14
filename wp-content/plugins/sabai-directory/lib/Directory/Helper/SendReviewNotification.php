<?php
class Sabai_Addon_Directory_Helper_SendReviewNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Entity_Entity $review, $user = null, array $tags = array())
    {
        if (!$listing = $application->Content_ParentPost($review)) {
            return;
        }
        $bundle = $application->Entity_Bundle($review);
        $tags += $application->Entity_TemplateTags($review, 'review_') + $application->Entity_TemplateTags($listing, 'listing_');
        if (!isset($user)) {
            $user = $application->Entity_Author($review);
        } elseif ($user === false) {
            $user = null; // do not set author, for flagged notification
        }
        foreach ((array)$name as $notification_name) {
            $application->System_SendEmail($bundle->addon, 'review_' . $notification_name, $tags, $user);
        }
    }
}