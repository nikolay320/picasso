<?php
class Sabai_Addon_Directory_Helper_SendLeadNotification extends Sabai_Helper
{
    public function help(Sabai $application, $name, Sabai_Addon_Entity_Entity $lead, $user = null, $sender = null, array $tags = array())
    {
        if (!$listing = $application->Content_ParentPost($lead)) {
            return;
        }
        $bundle = $application->Entity_Bundle($lead);
        $tags += array('{lead_url}' => $application->Url('/' . $application->getAddon('Directory')->getSlug('dashboard') . '/leads', array('lead_id' => $lead->getId())));
        $tags += $application->Entity_TemplateTags($lead, 'lead_', 0);
        $tags += $application->Entity_TemplateTags($listing, 'listing_');
        if (isset($sender)) {
            $options = array(
                'from' => sprintf('%s via %s', $sender->name, $application->getPlatform()->getSiteName()),
                'headers' => array(
                    sprintf('Reply-To: %s <%s>', $sender->name, $sender->email)
                ),
            );
        } else {
            $options = array();
        }
        foreach ((array)$name as $notification_name) {
            $application->System_SendEmail($bundle->addon, 'lead_' . $notification_name, $tags, $user, $options);
        }
    }
}
