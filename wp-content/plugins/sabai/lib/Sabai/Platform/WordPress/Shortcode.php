<?php
class Sabai_Platform_WordPress_Shortcode
{
    private $_count = 0, $_isInitialized = false, $_isFilterAdded = false,
        $_content = array(), $_placeholders = array();
    
    public function render($path, array $attributes = array())
    {
        ++$this->_count;
        $id = md5($path . serialize($attributes) . $this->_count);
        if (isset($this->_content[$id])) {
            return $this->_content[$id];
        }
        
        $platform = Sabai_Platform_WordPress::getInstance();
        
        try {
            // Init Sabai
            $sabai = $platform->getSabai(true, true);
            if (!$this->_isInitialized) {
                $sabai->getHelperBroker()->setHelper('LinkToRemote', array(new Sabai_Platform_WordPress_LinkToRemoteHelper(), 'help'));
                $this->_isInitialized = true;
            }
        
            // Create context
            $container = 'sabai-embed-wordpress-shortcode-' . $this->_count;
            $context = new Sabai_Context();
            $context->setContainer('#' . $container)
                ->setRequest(new Sabai_Request(true, true))
                ->setAttributes($attributes)
                ->addTemplateDir($platform->getAssetsDir() . '/templates');
        
            // Run Sabai
            $response = $sabai->run(new Sabai_MainRoutingController(), $context, $path);
            
            // Render output
            if ($context->isView()) {
                $response->setInlineLayoutHtmlTemplate('Sabai/Platform/WordPress/layout/shortcode_inline.html.php');
                $placeholder = '<div id="' . $container . '"></div>';
                ob_start();
                $response->send($context);
                $content = '<div id="' . $container . '" class="sabai sabai-embed">' . ob_get_clean() . '</div>';
                if (!defined('SABAI_WORDPRESS_SHORTCODE_USE_RETURN')
                    || (SABAI_WORDPRESS_SHORTCODE_USE_RETURN && !empty($attributes['return']))
                ) {
                    $ret = array();
                    if ($header = $platform->getHeaderHtml()) {
                        $ret[] = $header;
                        $platform->clearHeader();
                    }
                    if ($js = $platform->getJsHtml()) {
                        $ret[] = $js;
                        $platform->clearJs();
                    }
                    $ret[] = $content;
                    return implode(PHP_EOL, $ret);
                }
                if (!$this->_isFilterAdded) {
                    add_filter('the_content', array($this, 'filter'), 999999);
                    $this->_isFilterAdded = true;
                }
                $placeholder = '<div id="' . $container . '"></div>';
                $this->_content[$id] = $content;
                $this->_placeholders[$id] = $placeholder;
                return $placeholder;
            } elseif ($context->isError()) {
                $ret = array();
                $error = $response->getError($context);
                foreach ($error['messages'] as $message) {
                    $ret[] = '<div class="sabai-alert sabai-alert-danger">' . $message . '</div>';
                }
                return implode(PHP_EOL, $ret);
            } elseif ($context->isRedirect()) {
                return sprintf(
                    '<script type="text/javascript">jQuery(document).ready(function(){window.location.replace("%s");});</script><p>%s</p><p>%s</p><div><a class="sabai-btn sabai-btn-default">%s</a></div>',
                    $context->getRedirectUrl(),
                    ($message = $context->getRedirectMessage()) ? $message : __('Redirecting...', 'sabai'),
                    __('If you are not redirected automatically, please click the button below:', 'sabai'),
                    __('Continue', 'sabai')
                );
            } else {
                return '';
            }
        } catch (Exception $e) {
            if (is_super_admin() || (defined('WP_DEBUG') && WP_DEBUG)) {
                // Print trace if admin
                return sprintf('<p>%s</p><p><pre>%s</pre></p>', Sabai::h($e->getMessage()), Sabai::h($e->getTraceAsString()));
            }
            return sprintf('<p>%s</p>', 'An error occurred while processing the request. Please contact the administrator of the website for further information.');
        }
    }
    
    public function filter($content)
    {
        if (!empty($this->_placeholders)) {
            foreach ($this->_placeholders as $id => $placeholder) {
                $content = str_replace($placeholder, $this->_content[$id], $content);
            }
            $platform = Sabai_Platform_WordPress::getInstance();
            if ($js = $platform->getJsHtml()) {
                $content = $js . PHP_EOL . $content;
                $platform->clearJs();
            }
            if ($header = $platform->getHeaderHtml()) {
                $content = $header . PHP_EOL . $content;
                $platform->clearHeader();
            }
        }
        return $content;
    }
}
