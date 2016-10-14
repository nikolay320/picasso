<?php
class Sabai_Addon_Content_Helper_CountPendingPosts extends Sabai_Helper
{
    public function help(Sabai $application, $force = false)
    {
        if ($force || (false === $count = $application->getPlatform()->getCache('content_pending_count'))) {
            // Count the number of pending content for each content type
            $count = $application->Entity_Query('content')
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PENDING)
                ->groupByProperty('post_entity_bundle_name')
                ->count();
            $application->getPlatform()->setCache($count, 'content_pending_count');
        }
        return $count;
    }
}