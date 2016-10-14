<?php
class Sabai_Addon_Voting_Helper_SpamThreshold extends Sabai_Helper
{
    /**
     * Returns the current spam threshold score for a given entity. 
     * @param Sabai $application
     * @param Sabai_Addon_Entity_IEntity $entity
     * @return mixed 
     */
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity)
    {
        $spam_threshold = Sabai_Addon_Voting::FLAG_VALUE_OFFENSIVE * 2;
        // The spam threshold score can be higher/lower depending on the current vote score of the content
        if ($votes = $entity->getSingleFieldValue('voting_updown', 'sum')) {
            $spam_threshold += $votes / 3;
        }
        
        return $spam_threshold;
    }
}