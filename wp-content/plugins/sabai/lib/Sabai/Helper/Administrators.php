<?php
class Sabai_Helper_Administrators extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return $application->getPlatform()->getUsersByUserRole(array_keys($application->AdministratorRoles()));
    }
}