<?php
class Sabai_Addon_Questions_Helper_UserReputation extends Sabai_Helper
{
    public function help(Sabai $application, SabaiFramework_User_Identity $identity, $addonName = null)
    {
        if (!isset($addonName)) {
            $addonName = $application->getCurrentAddon();
        }
        return (int)$application->getPlatform()->getUserMeta($identity->id, strtolower($addonName) . '_reputation');
    }
}