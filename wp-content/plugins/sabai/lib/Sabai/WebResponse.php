<?php
class Sabai_WebResponse extends Sabai_Response
{
    private $_layoutHtmlTemplate, $_inlineLayoutHtmlTemplate;

    public function send(SabaiFramework_Application_Context $context)
    {
        $this->_application->Action('sabai_webresponse_send', array($context));

        parent::send($context);
    }

    public function setInlineLayoutHtmlTemplate($template)
    {
        $this->_inlineLayoutHtmlTemplate = $template;

        return $this;
    }

    public function setLayoutHtmlTemplate($template)
    {
        $this->_layoutHtmlTemplate = $template;

        return $this;
    }

    protected function _sendSuccess(SabaiFramework_Application_Context $context)
    {
        $success_url = (string)$this->_getSuccessUrl($context);
        if ($context->getRequest()->isAjax()) {
            if (!$success_url) {
                $context->setFlashEnabled(false);
            }
            if ($attributes = $context->getSuccessAttributes()) {
                foreach (array_keys($attributes) as $k) {
                    if ($attributes[$k] instanceof SabaiFramework_Application_Url) {
                        $attributes[$k]['separator'] = '&';
                        $attributes[$k] = (string)$attributes[$k];
                    }
                }
            }
            // Send success response as json
            self::sendStatusHeader(278, 'Success');
            self::sendHeader('Content-type', 'application/json; charset=' . $context->getCharset());
            echo json_encode(array_merge(
                $attributes,
                array(
                    'url' => $success_url,
                    'messages' => $context->getFlash(),
                )
            ));

            return;
        }

        // Redirect
        self::sendHeader('Location', $success_url);
    }
    
    public function getError(SabaiFramework_Application_Context $context)
    {
        $url = $context->getErrorUrl();
        $default_message = '';
        switch ($context->getErrorType()) {
            case Sabai_Context::ERROR_BAD_REQUEST:
                $default_message = __('Your browser sent a request that this server could not understand.', 'sabai');
                break;
            case Sabai_Context::ERROR_UNAUTHORIZED:
                if (!$url) {
                    $url = $context->getRequest()->url();
                }
                if ($context->getRequest()->isAjax()
                    || strpos($context->getContainer(), '#sabai-embed') === 0
                ) {
                    $default_message = sprintf(
                        __('You must <a href="%s" class="sabai-login popup-login">login</a> to perform the requested action.', 'sabai'),
                        $this->_application->LoginUrl($this->_application->Url($url))
                    );
                    $url = null;
                } else {
                    $url = $this->_application->getPlatform()->getLoginUrl((string)$this->_application->Url($url));
                }
                break;
            case Sabai_Context::ERROR_FORBIDDEN:
                $default_message = __('Your request may not be processed.', 'sabai');
                break;
            case Sabai_Context::ERROR_NOT_FOUND:
                $default_message = __('The requested page was not found.', 'sabai');
                break;
            case Sabai_Context::ERROR_METHOD_NOT_ALLOWED:
                $default_message = __('The requested method is not allowed.', 'sabai');
                break;
            case Sabai_Context::ERROR_NOT_ACCEPTABLE:
                $default_message = __('The requested page is not acceptable by the browser.', 'sabai');
                break;
            case Sabai_Context::ERROR_NOT_IMPLEMENTED:
                $default_message = __('The requested method is not implemented.', 'sabai');
                break;
            case Sabai_Context::ERROR_SERVICE_UNAVAILABLE:
                $default_message = __('The server is currently unable to handle the request. Please try again later.', 'sabai');
                break;
            case Sabai_Context::ERROR_INTERNAL_SERVER_ERROR:
            default:
                $default_message = __('The server encountered an error processing your request.', 'sabai');
        }

        // Use default error message if none set
        if (!$message = $context->getErrorMessage()) {
            $message = $default_message;
        }
        
        // Always convert URL to SabaiFramework_Application_Url
        if (isset($url)) {
            $url = $this->_application->Url($url);
        }
        
        return array('url' => $url, 'messages' => array($message));
    }

    protected function _sendError(SabaiFramework_Application_Context $context)
    {
        $error = $this->getError($context);

        if ($context->getRequest()->isAjax()) {
            // Save error message as flash if redirection URL is set
            if (isset($error['url']) && !empty($error['messages'])) {
                foreach ($error['messages'] as $message) {
                    $context->addFlash($message, Sabai_Context::FLASH_ERROR);
                }
            }

            // Send error response as json
            self::sendStatusHeader($context->getErrorType());
            self::sendHeader('Content-type', 'application/json; charset=' . $context->getCharset());
            echo json_encode(array(
                'messages' => $error['messages'],
                'url' => (string)$error['url'],
            ));

            return;
        }

        if (!isset($error['url'])) {
            if ((string)$context->getRoute() === '/') {
                // An error occurred on the top page. Throw an exception to prevent redirection loop.
                throw new RuntimeException(__('The server encountered an error processing your request.', 'sabai'));
            }
            $error['url'] = $this->_application->Url(); // redirect to the top page
        }

        foreach ($error['messages'] as $message) {
            $context->addFlash($message, Sabai_Context::FLASH_ERROR);
        }
        self::sendHeader('Location', $error['url']);
    }

    protected function _sendView(SabaiFramework_Application_Context $context)
    {
        $template = new Sabai_Template(
            $this->_application,
            $context->getTemplateDirs(),
            $this->_getGlobalTemplateVars($context)
        );
        
        $this->_application->Action('sabai_webresponse_render', array($context, $template));
        // Invoke controller specific event
        $event = $this->_application->getPlatform()->isAdmin()
            ? $context->getRoute()->getAddon() . '_admin_' . $context->getRoute()->getControllerName()
            : $context->getRoute()->getAddon() . '_' . $context->getRoute()->getControllerName();
        $this->_application->Action('sabai_webresponse_render' . strtolower($event), array($context, $template));

        // Make sure a template file exists, otherwise return 404 error
        if (!$context->hasTemplate()) {
            $context->setNotFoundError();
            $this->_sendError($context);

            return;
        }
        
        switch ($context->getContentType()) {
            case 'xml':
                if (!headers_sent()) {
                    self::sendStatusHeader(200);
                    self::sendHeader('Content-Type', sprintf('text/xml; charset=%s', $context->getCharset()));
                    $this->_sendHeaders();
                }
                $this->_printXml($context, $template);
                return;

            case 'json':
                if (!headers_sent()) {
                    self::sendStatusHeader(200);
                    self::sendHeader('Content-Type', sprintf('application/json; charset=%s', $context->getCharset()));
                    $this->_sendHeaders();
                }
                $this->_printJson($context, $template);
                return;

            default:
                if (!headers_sent()) {
                    self::sendStatusHeader(200);
                    self::sendHeader('Content-Type', sprintf('text/html; charset=%s', $context->getCharset()));
                    $this->_sendHeaders();
                }
                $this->_printHtml($context, $template);
        }
    }

    private function _printXml(Sabai_Context $context, Sabai_Template $template)
    {
        $this->_application->Action('sabai_web_response_render_xml', array($context, $template));

        echo '<?xml version="1.0" encoding="' . Sabai::h($context->getCharset()) . '"?>';
        
        $template->displayTemplate(array_reverse($context->getTemplates()), $context->getAttributes(), '.xml');
    }

    private function _printJson(Sabai_Context $context, Sabai_Template $template)
    {
        $this->_application->Action('sabai_web_response_render_json', array($context, $template));
        
        $template->displayTemplate(array_reverse($context->getTemplates()), $context->getAttributes(), '.json');
    }

    private function _printHtml(Sabai_Context $context, Sabai_Template $template)
    {
        // Fetch content
        $content = $template->renderTemplate(array_reverse($context->getTemplates()), $context->getAttributes());
        
        $this->_application->Action('sabai_web_response_render_html', array($context, &$content));

        // No layout if the requested content is an HTML fragment
        if (!isset($this->_inlineLayoutHtmlTemplate) && !isset($this->_layoutHtmlTemplate)) {
            // No layout templates, so output content directly
            echo $content;
            return;
        }
        
        $vars = $this->_getLayoutTemplateVars($context, $content) + $template->getVars();

        $this->_application->Action('sabai_web_response_render_html_layout', array($context, &$content, &$vars));
        
        // Add inline layout?
        if (isset($this->_inlineLayoutHtmlTemplate)) {
            if (!isset($this->_layoutHtmlTemplate)) {
                // No layout template, so output content directly
                $this->_include($this->_inlineLayoutHtmlTemplate, $vars);
                return;
            }
            // Fetch content with inline layout
            ob_start();
            $this->_include($this->_inlineLayoutHtmlTemplate, $vars);
            $vars['CONTENT'] = ob_get_clean();
        }
       
        $this->_include($this->_layoutHtmlTemplate, $vars);
    }

    private function _getLayoutTemplateVars(Sabai_Context $context, $content)
    {
        // Init inline tabs
        if ($inline_tabs = $context->getInlineTabs()) {
            // Set the first tab as current if no valid current tab specified
            if ((!$inline_tab_current = $context->getRequest()->asStr(Sabai_Request::$inlineTabParam, false))
                || !isset($inline_tabs[$inline_tab_current])
            ) {
                $inline_tab_names = array_keys($inline_tabs);
                $inline_tab_current = array_shift($inline_tab_names);
            }
            foreach ($inline_tabs as $tab_name => $tab) {
                if ($tab['hide_empty']) {
                    if (!$inline_tabs[$tab_name]['content'] = $this->_application->ImportRoute('#sabai-inline-content-' . $tab_name, $inline_tabs[$tab_name]['route'], $context)) {
                        unset($inline_tabs[$tab_name]);
                        continue;
                    }
                }
            }
        } else {
            $inline_tab_current = null;
        }
        $info = $context->getInfo();
        if (!$title = $context->getTitle()) {
            if (!empty($info) && ($_info = array_values($info))) {
                $_info = array_pop($_info);
                $title = $_info['title'];
            } else {
                $title = '';
            }
        }

        return array(
            'CONTENT' => $content,
            'CONTENT_MAIN' => $content,
            'CONTENT_URL' => ($url = $context->getUrl()) ? $url : $this->_application->Url($context->getRoute()),
            'CONTENT_SUMMARY' => $context->getSummary(),
            'CONTENT_TITLE' => $title,
            'CONTENT_MENU' => $context->getMenus(),
            'CONTENT_BREADCRUMBS' => $info,
            'TAB_CURRENT' => $context->getCurrentTab(),
            'TABS' => $context->getTabs(),
            'TAB_MENU' => $context->getTabMenus(),
            'TAB_BREADCRUMBS' => $context->getTabInfo(),
            'INLINE_TABS' => $inline_tabs,
            'INLINE_TAB_CURRENT' => $inline_tab_current,
            'CHARSET' => $context->getCharset(),
            'HTML_HEAD_TITLE' => $context->getHtmlHeadTitle(),
        );
    }

    private function _include()
    {
        extract(func_get_arg(1), EXTR_SKIP);
        return include func_get_arg(0);
    }
}