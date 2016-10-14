<?php
class Sabai_Addon_System_Helper_UserActivity extends Sabai_Helper
{
    /**
     * Returns all sluggable routes
     * @param Sabai $application
     * @param SabaiFramework_User_Identity|int
     */
    public function help(Sabai $application, $user)
    {
        if (!$user instanceof SabaiFramework_User_Identity) {
            $user = $application->UserIdentity($user);
        }
        $counts = $application->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIs('post_user_id', $user->id)
            ->groupByProperty('post_entity_bundle_name')
            ->count();
        $activity = $application->Filter('system_user_activity', array(), array($user, $counts));
        foreach (array_keys($activity) as $key) {
            foreach ((array)@$activity[$key]['stats'] as $stat_name => $stat) {
                if (isset($stat['count'])) {
                    $count = $stat['count'];
                } elseif (isset($counts[$stat_name])) {
                    $count = $counts[$stat_name];
                } else {
                    $count = 0;
                }
                $activity[$key]['stats'][$stat_name] += array(
                    'count' => $count,
                    'formatted' => sprintf($stat['format'], '<strong>' . $count . '</strong>'),
                );
                if (isset($stat['url'])) {
                    $activity[$key]['stats'][$stat_name]['url'] = $application->Url($stat['url']);
                }
            }
        }
        return $activity;
    }
}