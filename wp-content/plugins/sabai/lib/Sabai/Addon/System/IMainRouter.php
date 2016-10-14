<?php
interface Sabai_Addon_System_IMainRouter extends Sabai_Addon_System_IRouter
{
    public function systemGetMainRoutes();
    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route);
    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route);
}