<?php
class Sabai_AdminRoutingController extends Sabai_RoutingController
{    
    protected function _getDefaultRoute()
    {
        return new Sabai_Route('/', array('controller_class' => 'Sabai_AdminIndex'));
    }

    protected function _getAddonRoutes($rootPath)
    {
        return $this->_application->getAddon('System')->getAdminRoutes($rootPath);
    }

    protected function _processAccessCallback(Sabai_Context $context, array &$route, $accessType)
    {
        return $this->_application->getAddon($route['callback_addon'])->systemOnAccessAdminRoute(
            $context,
            $route['callback_path'],
            $accessType,
            $route
        );
    }

    protected function _processTitleCallback(Sabai_Context $context, array $route, $titleType)
    {
        return $this->_application->getAddon($route['callback_addon'])->systemGetAdminRouteTitle(
            $context,
            $route['callback_path'],
            $route['title'],
            $titleType,
            $route
        );
    }
    
    protected function _processRouteData(array &$routeData)
    {
        $routeData['controller_class'] = 'Sabai_Addon_' . $routeData['controller_addon'] . '_Controller_Admin_' . $routeData['controller'];
        $routeData['controller_file'] = $this->_application->getAddonPath($routeData['controller_addon']) . '/Controller/Admin/' . $routeData['controller'] . '.php';
    }

    public function execute(SabaiFramework_Application_Context $context)
    {
        if (!$this->getPlatform()->isAdmin()) {
            $context->setForbiddenError();
            return;
        }
        if ($this->getUser()->isAnonymous()) {
            $context->setUnauthorizedError($this->AdminUrl());
            return;
        }
        if (!$this->getUser()->isAdministrator()) {
            $context->setForbiddenError();
            return;
        }

        parent::execute($context);
    }
}