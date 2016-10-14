<?php
class Sabai_Addon_Content_Helper_RestoreReferencingPosts extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds)
    {
        $referencing_entity_ids = array();
        foreach ($application->Entity_Query()
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_TRASHED)
            ->fieldIsIn('content_reference', $entityIds)
            ->fetch()
        as $entity) {
            $referencing_entity_ids[] = $entity->getId();
        }
        if (!empty($referencing_entity_ids)) {
            $application->Content_RestorePosts($referencing_entity_ids);
        }
    }
}