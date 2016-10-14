<?php
class Sabai_Addon_Directory_Helper_ListingOwner extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        $application->Entity_LoadFields($entity);
        
        if (!$claimed_by = @$entity->directory_claim[0]['claimed_by']) {
            return;
        }
        
        if (!empty($entity->directory_claim[0]['expires_at'])
            && $entity->directory_claim[0]['expires_at'] <= time()
        ) {
            return;
        }
        
        return $application->UserIdentity($claimed_by);
    }
}