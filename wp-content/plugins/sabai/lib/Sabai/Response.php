<?php
abstract class Sabai_Response extends SabaiFramework_Application_HttpResponse
{
    /**
     * Call an application helper
     */
    public function __call($name, $args)
    {
        return $this->_application->getHelperBroker()->callHelper($name, $args);
    }

    public function send(SabaiFramework_Application_Context $context)
    {
        $this->_application->Action('sabai_response_send', array($context, $this));

        parent::send($context);

        $this->_application->Action('sabai_response_send_complete', array($context));
    }

    protected function _sendRedirect(SabaiFramework_Application_HttpContext $context)
    {
        switch ($context->getRedirectType()) {
            case Sabai_Context::REDIRECT_PERMANENT:
                self::sendStatusHeader(301);
                self::sendHeader('Location', (string)$this->_getRedirectUrl($context));
                return;
                
            case Sabai_Context::REDIRECT_TEMPORARY:
            default:
                self::sendStatusHeader(302);
                self::sendHeader('Location', (string)$this->_getRedirectUrl($context));
        }
    }

    protected function _getGlobalTemplateVars(Sabai_Context $context)
    {
        return array(
            'CONTEXT' => $context,
            'CONTEXT_NAME' => $this->_application->ControllerName($context->getRoute()->getController()),
            'CURRENT_ROUTE' => $context->getRoute(),
            'CURRENT_CONTAINER' => $context->getContainer(),
            'CURRENT_USER' => $this->_application->getUser(),
            'SITE_NAME' => $this->_application->getPlatform()->getSiteName(),
            'SITE_URL' => $this->_application->getPlatform()->getHomeUrl(),
            'SITE_EMAIL' => $this->_application->getPlatform()->getSiteEmail(),
            'SITE_ADMIN_URL' => $this->_application->getPlatform()->getSiteAdminUrl(),
            'IS_AJAX' => $context->getRequest()->isAjax(),
            'IS_EMBED' => strpos($context->getContainer(), '#sabai-embed') === 0,
        );
    }

    protected function _getSuccessUrl(Sabai_Context $context, $separator = '&')
    {
        if (!$url = $context->getSuccessUrl()) {
            $url = Sabai_Request::url(); // use the current URL
        } else {
            $url = $this->_application->Url($url); // converts to an SabaiFramework_URL object
            $url['separator'] = $separator;
        }

        return $url;
    }

    protected function _getRedirectUrl(Sabai_Context $context, $separator = '&')
    {
        if (!$url = $context->getRedirectUrl()) {
            $url = Sabai_Request::url(); // use the current URL
        } else {
            $url = $this->_application->Url($url); // convert to an SabaiFramework_URL object
            $url['separator'] = $separator;
        }

        return $url;
    }
}