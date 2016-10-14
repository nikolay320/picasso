<?php
abstract class Sabai_Controller implements SabaiFramework_Application_Controller
{
    protected $_application, $_parent, $_route;

    /* Start implementation of SabaiFramework_Application_Controller */

    public function setRoute($route)
    {
        $this->_route = $route;

        return $this;
    }

    final public function setApplication(SabaiFramework_Application $application)
    {
        $this->_application = $application;

        return $this;
    }

    final public function getApplication()
    {
        return $this->_application;
    }

    final public function setParent(SabaiFramework_Application_RoutingController $controller)
    {
        $this->_parent = $controller;

        return $this;
    }

    final public function execute(SabaiFramework_Application_Context $context)
    {
        $this->_doExecute($context);
    }

    /* End implementation of SabaiFramework_Application_Controller */

    final public function __call($method, $args)
    {
        return call_user_func_array(array($this->_application, $method), $args);
    }
    
    final public function __get($name) {
        return $this->_application->getAddon($name);
    }

    protected function _checkToken(Sabai_Context $context, $tokenId, $reuseable = false, $tokenName = Sabai_Request::PARAM_TOKEN)
    {
        if (!$token = $context->getRequest()->asStr($tokenName, false)) {
            $context->setBadRequestError();
            return false;
        }
        if (!$this->_application->TokenValidate($token, $tokenId, $reuseable)) {
        //if (!SabaiFramework_Token::validate($token, $tokenId, $reuseable)) {
            $context->setError(__('Token has expired. Please reload the page and try again.', 'sabai'), null, Sabai_Context::ERROR_FORBIDDEN);
            return false;
        }
        return true;
    }

    abstract protected function _doExecute(Sabai_Context $context);
}