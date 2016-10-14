<?php
class Sabai_Addon_Entity_Helper_Entity extends Sabai_Helper
{
    public function help(Sabai $application, $entityType, $entityId, $loadEntityFields = true)
    {
        if ($entity = $application->Entity_TypeImpl($entityType)->entityTypeGetEntityById($entityId)) {
            if ($loadEntityFields) {
                $application->Entity_LoadFields($entity);
            }
        }
        return $entity;
    }
}