<?php
class Sabai_Addon_Content_Helper_ParentPost extends Sabai_Helper
{    
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $loadEntityFieldValues = true)
    {        
        if (!$entity->isFieldsLoaded()) {
            $application->Entity_LoadFields($entity);
        }
        $parent_post = $entity->getSingleFieldValue('content_parent');
        if (!$parent_post) {
            return;
        }
        if ($loadEntityFieldValues) {
            $application->Entity_LoadFields($parent_post);
        }
        return $parent_post;
    }
}