<?php
class Sabai_Addon_System_Helper_RouteExists extends Sabai_Helper
{
    public function help(Sabai $application, $path, $type = null)
    {
        switch ($type) {
            case 'admin':
                $model = 'Adminroute';
                break;
            default:
                $model = 'Route';
                break;
        }
        
        return $application->getAddon('System')->getModel($model)->path_is($path)->count() ? true : false;
    }
}