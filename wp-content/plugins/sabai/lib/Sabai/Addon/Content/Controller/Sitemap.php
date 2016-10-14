<?php
class Sabai_Addon_Content_Controller_Sitemap extends Sabai_Addon_System_Controller_Sitemap
{
    protected function _getCacheId(Sabai_Context $context)
    {
        return 'content_sitemap_' . $context->bundle->name;
    }
    
    protected function _getQuery(Sabai_Context $context)
    {
        return $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $context->bundle->name)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
    }
    
    protected function _getEntityLastModified(Sabai_Addon_Entity_Entity $entity)
    {
        return empty($entity->content_activity[0]['active_at']) ? $entity->getTimestamp() : $entity->content_activity[0]['active_at'];
    }
}