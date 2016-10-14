<?php
abstract class SabaiFramework_Application_RoutingController implements SabaiFramework_Application_Controller
{
    private $_autoloadControllers = false;

    protected $_route;

    /**
     * Constructor
     *
     * @param bool $autoloadControllers
     * @return SabaiFramework_Application_RoutingController
     */
    protected function __construct($autoloadControllers = false)
    {
        $this->_autoloadControllers = $autoloadControllers;
    }

    /* Start implementation of SabaiFramework_Application_Controller */

    public function setRoute($route)
    {
        $this->_route = $route;

        return $this;
    }

    public function execute(SabaiFramework_Application_Context $context)
    {
        if (isset($this->_route)
            && ($route = $this->_isRoutable($context, $this->_route))
        ) {
            if ($forward = $route->isForward()) { // forward to another route?
                // Re-route to the forwarded route
                $this->forward($forward, $context);
            } else {
                $this->_doExecute($context, $route);
            }
        } else {
            if (!$context->isView()) return;

            $this->_doExecute($context, $this->_getDefaultRoute());
        }
    }

    /**
     * Forwards request to another route
     *
     * @param string $forward
     * @param SabaiFramework_Application_Context $context
     */
    public function forward($forward, SabaiFramework_Application_Context $context)
    {
        if ($route = $this->_isRoutable($context, $forward)) {
            // Is this route being forwarded to another route?
            if ($_forward = $route->isForward()) {
                // Recursive forwarding is not allowed
                throw new SabaiFramework_Exception(
                    sprintf('Recursive request forwarding detected. The request forwarded to route %s may not be forwarded to another route %s.', $forward, $_forward)
                );
            } else {
                $this->_doExecute($context, $route);
            }
        } else {
            if (!$context->isView()) return;

            if (isset($this->_parent)) {
                $this->_parent->forward($forward, $context);

                return;
            }

            $this->_doExecute($context, $this->_getDefaultRoute());
        }
    }

    /**
     * Runs the controller if any
     *
     * @param SabaiFramework_Application_Context $context
     * @param SabaiFramework_Application_Route $route
     */
    protected function _doExecute(SabaiFramework_Application_Context $context, SabaiFramework_Application_Route $route)
    {
        // Set current route
        $context->setRoute($route);

        // Set request parameters if any
        foreach ($route->getParams() as $key => $value) {
            $context->getRequest()->set($key, $value);
        }

        // Fetch controller instance
        if (!$controller = $route->getController()) return; // no controller defined

        if (is_string($controller)
            && (!$controller = $this->_getController($controller, $route->getControllerArgs(), $route->getControllerFile())) // controller does not exist
        ) {
            return;
        }

        $this->_executeController($context, $controller);
    }

    protected function _executeController(SabaiFramework_Application_Context $context, SabaiFramework_Application_Controller $controller)
    {
        $controller->setApplication($this->getApplication())->setParent($this)->setRoute($this->_route)->execute($context);
    }

    /**
     * Gets a controller instance
     *
     * @param string $controllerClass
     * @param array $controllerArgs
     * @param string $controllerFile
     * @return SabaiFramework_Application_Controller
     */
    protected function _getController($controllerClass, array $controllerArgs, $controllerFile)
    {
        if (!empty($controllerFile)) {
            require_once $controllerFile;
        } else {
            if (!class_exists($controllerClass, $this->_autoloadControllers)) {
                return false;
            }
        }

        if (empty($controllerArgs)) {
            return new $controllerClass();
        }

        $reflection = new ReflectionClass($controllerClass);

        return $reflection->newInstanceArgs($controllerArgs);
    }

    /**
     * Returns a SabaiFramework_Application_Route instance
     *
     * @return mixed SabaiFramework_Application_Route or false
     * @param SabaiFramework_Application_Context $context
     * @param string $route
     */
    abstract protected function _isRoutable(SabaiFramework_Application_Context $context, $route);
    /**
     * Returns the default route
     *
     * @return mixed SabaiFramework_Application_Route
     */
    abstract protected function _getDefaultRoute();
}