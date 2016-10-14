<?php
abstract class SabaiFramework_Application_Response
{
    protected $_application;
    
    final public function setApplication(SabaiFramework_Application $application)
    {
        $this->_application = $application;
        
        return $this;
    }
    
    public function send(SabaiFramework_Application_Context $context)
    {
        switch ($context->getStatus()) {
            case SabaiFramework_Application_Context::STATUS_SUCCESS:
                $this->_sendSuccess($context);
                return;

            case SabaiFramework_Application_Context::STATUS_ERROR:
                $this->_sendError($context);
                return;
                
            default:
                $this->_sendView($context);
        } 
    }
    
    abstract protected function _sendSuccess(SabaiFramework_Application_Context $context);
    abstract protected function _sendError(SabaiFramework_Application_Context $context);
    abstract protected function _sendView(SabaiFramework_Application_Context $context);
}