<?php
abstract class SabaiFramework_Application_HttpResponse extends SabaiFramework_Application_Response
{
    private static $_codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );

    protected $_headers = array();

    public function setHeader($name, $value)
    {
        $this->_headers[$name] = $value;

        return $this;
    }

    public function hasHeader($name)
    {
        return isset($this->_headers[$name]);
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

            case SabaiFramework_Application_HttpContext::STATUS_REDIRECT:
                $this->_sendRedirect($context);
                return;

            default:
                $this->_sendView($context);
        }
    }

    protected function _sendRedirect(SabaiFramework_Application_HttpContext $context)
    {
        self::sendHeader('Location', $context->getRedirectUrl());
    }

    protected function _sendHeaders()
    {
        foreach ($this->_headers as $name => $value) {
            self::sendHeader($name, $value);
        }
    }

    public static function sendHeader($name, $value)
    {
        header(str_replace(array("\r", "\n"), '', $name . ': ' . $value));
    }

    public static function sendStatusHeader($code, $message = null)
    {
        if (!isset(self::$_codes[$code])) {
            // Custom status code requires status message
            if (!isset($message)) return;
        } else {
            $message = self::$_codes[$code];
        }

        // Fix for Squid
        $protocol = 'HTTP/1.1' !== $_SERVER['SERVER_PROTOCOL'] || 'HTTP/1.0' !== $_SERVER['SERVER_PROTOCOL'] ? 'HTTP/1.0' : $_SERVER['SERVER_PROTOCOL'];
        
        header(sprintf('%s %d %s', $protocol, $code, $message), true, $code);
    }
}