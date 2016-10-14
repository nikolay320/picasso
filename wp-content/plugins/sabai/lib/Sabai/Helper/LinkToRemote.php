<?php
class Sabai_Helper_LinkToRemote extends Sabai_Helper
{
    private static $_alwaysPost = false;
    
    public function __construct()
    {
        if (defined('SABAI_FIX_URI_TOO_LONG') && SABAI_FIX_URI_TOO_LONG) {
            self::$_alwaysPost = true;
        }
    }
    
    public function help(Sabai $application, $linkText, $update, $linkUrl, array $options = array(), array $attributes = array())
    {
        $link_url = $application->Url($linkUrl);
        $ajax_url = isset($options['url']) ? $application->Url($options['url']) : clone $link_url;
        $update = Sabai::h($update);

        // Add options
        $ajax_options = array();
        if (!empty($options['target'])) {
            $ajax_options[] = "target:'" . $options['target'] . "'";
        }
        if (isset($options['loadingImage']) && !$options['loadingImage']) $ajax_options[] = 'loadingImage:false';
        if (!empty($options['slide'])) $ajax_options[] = "effect:'slide'";
        if (!empty($options['scroll'])) $ajax_options[] = 'scroll:true';
        if (!empty($options['highlight'])) $ajax_options[] = 'highlight:true';
        if (!empty($options['replace'])) $ajax_options[] = 'replace:true';
        if (isset($options['width'])) $ajax_options[] = 'modalWidth:' . intval($options['width']);
        if (!empty($options['cache'])) $ajax_options[] = 'cache:true';
        if (!empty($options['sendData'])) {
            $ajax_options[] = 'onSendData:function(data, trigger){' . $options['sendData'] . '}';
        }
        if (!empty($options['success'])) {
            $ajax_options[] = 'onSuccess:function(result, target, trigger){' . $options['success'] . '}';
        }
        if (!empty($options['error'])) {
            $ajax_options[] = 'onError:function(result, target, trigger){' . $options['error'] . '}';
        }
        if (!empty($options['content'])) {
            $ajax_options[] = 'onContent:function(response, target, trigger){' . $options['content'] . '}';
        }
        if (!empty($options['post']) || self::$_alwaysPost) {
            $ajax_options[] = "type:'post'";
        }
        if (!empty($options['pushState'])) {
            $ajax_options[] = "pushState:true";
        }
        $ajax_url['separator'] = '&';
        $ajax_url = (string)$ajax_url;
        $ajax_options[] = "trigger:jQuery(this), container:'" . $update . "'";

        // Create attributes
        $attributes['onclick'] = 'SABAI.ajax({' . implode(',', $ajax_options) . '}); event.stopImmediatePropagation(); return false;';
        $attributes['data-sabai-remote-url'] = $ajax_url;
        
        return new Sabai_Link(SabaiFramework_Request_Http::isXhr() ? '#' : $link_url, $linkText, $options, $attributes); 
    }
}