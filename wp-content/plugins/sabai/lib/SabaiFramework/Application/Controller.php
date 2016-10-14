<?php
interface SabaiFramework_Application_Controller
{
    /**
     * Sets a string representation of the route to which requests should be routed
     *
     * @param $route string
     * @return SabaiFramework_Application_Controller Returns itself for chaining.
     */
    public function setRoute($route);
    /**
     * Sets an application instance
     *
     * @param SabaiFramework_Application $application
     * @return SabaiFramework_Application_Controller Returns itself for chaining.
     */
    public function setApplication(SabaiFramework_Application $application);
    /**
     * Gets an application instance
     * @return SabaiFramework_Application
     */
    public function getApplication();
    /**
     * Sets a parent controller
     *
     * @param SabaiFramework_Application_RoutingController $controller
     * @return SabaiFramework_Application_Controller Returns itself for chaining.
     */
    public function setParent(SabaiFramework_Application_RoutingController $controller);
    /**
     * Executes the controller
     *
     * @param SabaiFramework_Application_Context $context
     */
    public function execute(SabaiFramework_Application_Context $context);
}