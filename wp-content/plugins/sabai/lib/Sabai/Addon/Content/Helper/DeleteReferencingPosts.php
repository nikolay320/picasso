<?php
class Sabai_Addon_Content_Helper_DeleteReferencingPosts extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds)
    {
        $referencing_entities = $application->Entity_Query()
            ->fieldIsIn('content_reference', $entityIds)
            ->fetch();
        $application->getAddon('Entity')->deleteEntities('content', $referencing_entities);
    }
}