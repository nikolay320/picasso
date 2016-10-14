<?php
class Sabai_Addon_Voting_Helper_RatingSummary extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity)
    {
        return $application->getModel(null, 'Voting')
            ->getGateway('Vote')
            ->getRatingSummary($application->Entity_Bundle($entity)->id, $entity->getId());
    }
}