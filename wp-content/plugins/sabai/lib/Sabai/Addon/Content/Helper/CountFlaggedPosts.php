<?php
class Sabai_Addon_Content_Helper_CountFlaggedPosts extends Sabai_Helper
{
    public function help(Sabai $application, $force = false)
    {
        if ($force || (false === $count = $application->getPlatform()->getCache('content_flagged_post_count'))) {
            $count = $application->Entity_Query('content')
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->fieldIsGreaterThan('voting_flag', 0, 'count')
                ->groupByProperty('post_entity_bundle_name')
                ->count();
            $application->getPlatform()->setCache($count, 'content_flagged_post_count');
        }
        return $count;
    }
}