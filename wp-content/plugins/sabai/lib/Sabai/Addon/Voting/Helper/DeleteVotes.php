<?php
class Sabai_Addon_Voting_Helper_DeleteVotes extends Sabai_Helper
{
    public function help(Sabai $application, $entityIds, $tag)
    {
        $entities = array();
        if (!is_array($entityIds) && $entityIds instanceof Sabai_Addon_Content_Entity) {
            $entity = $entityIds;
            $application->Entity_Save($entity, array(
                'voting_' . $tag => false,
            ));
            $entities[$entity->getId()] = $entity;
        } else { 
            foreach ($application->Entity_Entities('content', (array)$entityIds) as $entity) {
                $application->Entity_Save($entity, array(
                    'voting_' . $tag => false,
                ));
                $entities[$entity->getId()] = $entity;
            }
        }
        if (empty($entities)) {
            return;
        }
        
        $application->getModel('Vote', 'Voting')->entityId_in(array_keys($entities))->tag_is($tag)->fetch()->delete(true);
        foreach ($entities as $entity) {
            // Notify vote deleted
            $application->Action('voting_entity_vote_deleted', array($entity, $tag));
            // Notify by vote tag, entity type, and bundle
            $application->Action('voting_entity_vote_deleted_' . $tag, array($entity));
            $application->Action('voting_' . $entity->getType() . '_entity_vote_deleted_' . $tag, array($entity));
            $application->Action('voting_' . $entity->getType() . '_' . $entity->getBundleType() . '_entity_vote_deleted_' . $tag, array($entity));
        }
    }
}