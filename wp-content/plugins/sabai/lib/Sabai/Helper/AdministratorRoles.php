<?php
class Sabai_Helper_AdministratorRoles extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        $ret = array();
        foreach ($application->getPlatform()->getUserRoles() as $role_name => $role_title) {
            if ($application->getPlatform()->isAdministratorRole($role_name)) {
                $ret[$role_name] = $role_title;
            }
        }
        return $ret;
    }
}
