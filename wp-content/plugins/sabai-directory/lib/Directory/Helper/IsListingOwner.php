<?php
class Sabai_Addon_Directory_Helper_IsListingOwner extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $checkExpired = true, SabaiFramework_User $user = null)
    {
        if (!isset($user)) {
            $user = $application->getUser();
        }
        
        if ($user->isAnonymous()) {
            return false;
        }
        
        $application->Entity_LoadFields($entity);
        
        if (!isset($entity->directory_claim[0]['claimed_by'])
            || $entity->directory_claim[0]['claimed_by'] !== $user->id
        ) {
            return false;
        }
        
        if (!$checkExpired || empty($entity->directory_claim[0]['expires_at'])) {
            return true;
        }
        
        return $entity->directory_claim[0]['expires_at'] > time() - $application->Entity_Addon($entity)->getConfig('claims', 'grace_period') * 86400;
    }
}