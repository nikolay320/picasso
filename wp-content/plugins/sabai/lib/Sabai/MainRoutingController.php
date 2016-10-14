<?php
class Sabai_MainRoutingController extends Sabai_RoutingController
{    
    protected function _getDefaultRoute()
    {
        return new Sabai_Route('/', array('controller_class' => 'Sabai_MainIndex'));
    }

    protected function _getAddonRoutes($rootPath)
    {
        return $this->_application->getAddon('System')->getMainRoutes($rootPath);
    }

    protected function _processAccessCallback(Sabai_Context $context, array &$route, $accessType)
    {
        return $this->_application->getAddon($route['callback_addon'])->systemOnAccessMainRoute(
            $context,
            $route['callback_path'],
            $accessType,
            $route
        );
    }

    protected function _processTitleCallback(Sabai_Context $context, array $route, $titleType)
    {
        return $this->_application->getAddon($route['callback_addon'])->systemGetMainRouteTitle(
            $context,
            $route['callback_path'],
            $route['title'],
            $titleType,
            $route
        );
    }
    
    protected function _processRouteData(array &$routeData)
    {
        $routeData['controller_class'] = 'Sabai_Addon_' . $routeData['controller_addon'] . '_Controller_' . $routeData['controller'];
        $routeData['controller_file'] = $this->_application->getAddonPath($routeData['controller_addon']) . '/Controller/' . $routeData['controller'] . '.php';
    }
}