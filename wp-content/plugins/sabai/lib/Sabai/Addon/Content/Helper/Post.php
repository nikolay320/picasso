<?php
class Sabai_Addon_Content_Helper_Post extends Sabai_Helper
{
    public function help(Sabai $application, $postId)
    {
        return $application->Entity_Entity('content', $postId);
    }
}