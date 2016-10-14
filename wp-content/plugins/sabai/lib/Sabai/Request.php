<?php
class Sabai_Request extends SabaiFramework_Request_Http
{
    // Pre-defined request parameter constants
    const PARAM_AJAX = '__ajax', PARAM_TOKEN = '__t', PARAM_CONTENT_TYPE = '__type';
    public static $inlineTabParam = 'tab';

    protected static $_url, $_isAjax;

    public static function isAjax()
    {
        if (!isset(self::$_isAjax)) {
            self::$_isAjax = isset($_REQUEST[self::PARAM_AJAX]) ? $_REQUEST[self::PARAM_AJAX] : parent::isXhr();
        }

        return self::$_isAjax;
    }

    public static function url()
    {
        if (isset(self::$_url)) return self::$_url;

        $request_url = parent::url();

        if (!$parsed = parse_url($request_url)) {
            self::$_url = $request_url;

            return self::$_url;
        }

        if (!empty($parsed['query']))  {
            $params = array();
            parse_str(rawurldecode($parsed['query']), $params);
            unset($params[self::PARAM_AJAX]); // remove special parameter specifying that this is an AJAX request
            $query_str = '?' . strtr(http_build_query($params), array('%7E' => '~', '+' => '%20')); // http_build_query does urlencode, so need a little adjustment for RFC1738 compat
        } else {
            $query_str = '';
        }

        self::$_url = sprintf(
            '%s://%s%s%s%s',
            $parsed['scheme'],
            !empty($parsed['port']) ? $parsed['host'] . ':' . $parsed['port'] : $parsed['host'],
            $parsed['path'],
            $query_str,
            !empty($parsed['fragment']) ? '#' . $parsed['fragment'] : ''
        );

        return self::$_url;
    }
}