<?php
class Sabai_Addon_Content_Helper_TrashReferencingPosts extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds, $type = Sabai_Addon_Content::TRASH_TYPE_OTHER, $reason = '', $trashedBy = null)
    {
        $referencing_entity_ids = array();
        foreach ($application->Entity_Query()
            ->propertyIsNot('post_status', Sabai_Addon_Content::POST_STATUS_TRASHED)
            ->fieldIsIn('content_reference', $entityIds)
            ->fetch()
        as $entity) {
            $referencing_entity_ids[] = $entity->getId();
        }
        if (!empty($referencing_entity_ids)) {
            $application->Content_TrashPosts($referencing_entity_ids, $type, $reason, $trashedBy);
        }
    }
}