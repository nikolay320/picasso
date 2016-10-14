<?php
class Sabai_Addon_Questions_Helper_UpdateUserReputation extends Sabai_Helper
{
    public function help(Sabai $application, $pointId, SabaiFramework_User_Identity $identity, $points, $addonName = null, array $data = array())
    {
        if ($identity->isAnonymous()) return;
        
        if (!isset($addonName)) {
            $addonName = $application->getCurrentAddon();
        }
        
        $option = strtolower($addonName) . '_reputation';
        $new_points = intval($application->getPlatform()->getUserMeta($identity->id, $option) + $points);
        if ($new_points < 0) $new_points = 0;
        $application->getPlatform()->setUserMeta($identity->id, $option, $new_points);
        
        $application->Action('questions_user_reputation_updated', array($pointId, $identity, $points, $addonName, $data));
        
        return $new_points;
    }
}