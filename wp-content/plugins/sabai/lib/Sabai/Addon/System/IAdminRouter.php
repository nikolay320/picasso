<?php
interface Sabai_Addon_System_IAdminRouter extends Sabai_Addon_System_IRouter
{
    public function systemGetAdminRoutes();
    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route);
    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route);
}