<?php
class Sabai_Helper_ModelEntities extends Sabai_Helper
{
    public function help(Sabai $application, $addon, $entity, array $entityIds)
    {
        return $application->getModel($entity, $addon)->fetchByIds($entityIds);
    }
}