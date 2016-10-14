<?php
class Sabai_Addon_Entity_Helper_RenderActivity extends Sabai_Helper
{    
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, array $settings = array())
    {
        $settings += array(
            'action_label' => __('%s posted %s', 'sabai'),
            'show_last_active' => true,
            'show_last_edited' => false,
            'last_edited_label' => __('last edited %s', 'sabai'),
            'last_active_label' => __('last active %s', 'sabai'),
            'permalink' => true,
        );
        $activity = $entity->getActivity();
        $entity_timestamp = $entity->getTimestamp();
        $datediff = $application->getPlatform()->getHumanTimeDiff($entity_timestamp);
        $datetime = $application->DateTime($entity_timestamp);
        if ($settings['permalink']) {
            $date = sprintf('<a href="%s" title="%s" rel="nofollow">%s</a>', $application->Entity_Url($entity), $datetime, $datediff);
        } else {
            $date = '<span title="' . $datetime . '">' . $datediff . '</span>';
        }
        $li = array(
            sprintf($settings['action_label'], $application->UserIdentityLinkWithThumbnailSmall($application->Entity_Author($entity)), $date),
        );
        if ($settings['show_last_active']) {
            if (!empty($activity['active_at']) && $activity['active_at'] != $entity_timestamp) {
                $li[] = '<i class="fa fa-clock-o"></i>' . sprintf($settings['last_active_label'], $application->getPlatform()->getHumanTimeDiff($activity['active_at']));
            }
        }
        if ($settings['show_last_edited']) {
            if (!empty($activity['edited_at']) && $activity['edited_at'] != $entity_timestamp) {
                $li[] = '<i class="fa fa-clock-o"></i>' . sprintf($settings['last_edited_label'], $application->getPlatform()->getHumanTimeDiff($activity['edited_at']));
            }
        }
        
        return '<ul class="sabai-entity-activity"><li>' . implode('</li><li>', $li) . '</li></ul>';
    }
}