<?php
class Sabai_Helper_Url extends Sabai_Helper
{
    public function help(Sabai $application, $route = '', array $params = array(), $fragment = '', $separator = '&amp;', $script = null, $secure = false)
    {
        if ($route instanceof SabaiFramework_Application_Url) return $route;

        if (is_array($route)) return $application->createUrl($route);

        if (is_string($route) && strlen($route) && strpos($route, '/') !== 0) {
            if (strpos($route, 'http') !== 0) {
                throw new Sabai_InvalidArgumentException(sprintf('Invalid route %s.', $route));
            }

            if (strpos($route, $application->getScriptUrl($application->getCurrentScriptName())) !== 0) {
                // Not a Sabai URL. Always return a local URL for security reason
                return $application->createUrl();
            }

            // Convert URL into an array which Sabai::createUrl() recognizes
            if (!$parsed = parse_url($route)) {
                throw new Sabai_InvalidArgumentException(sprintf('Invalid route %s.', $route));
            }
            $fragment = !isset($fragment) && isset($parsed['fragment']) ? $parsed['fragment'] : (string)$fragment;
            if (!empty($parsed['query']))  {
                $query = array();
                parse_str($parsed['query'], $query);
                unset($query[Sabai_Request::PARAM_AJAX]);
                $params += $query;
                if (isset($params[$application->getRouteParam()])) {
                    $route = $params[$application->getRouteParam()];
                    unset($params[$application->getRouteParam()]);

                    return $this->_getUrl($application, $route, $params, $fragment, $separator, $script, $secure);
                }
            }

            $url = $parsed['scheme'] . '://' . $parsed['host'];
            if (isset($parsed['port'])) {
                $url .= ':' . $parsed['port'];
            }
            if (isset($parsed['path'])) {
                $url .= $parsed['path']; 
            }
            return $application->createUrl(array(
                'script_url' => $url,
                'params' => $params,
                'fragment' => $fragment,
                'separator' => $separator,
                'secure' => $secure,
            ));
        }

        return $this->_getUrl($application, $route, $params, $fragment, $separator, $script, $secure);
    }

    protected function _getUrl($application, $route, $params, $fragment, $separator, $script, $secure)
    {
        return $application->createUrl(array(
            'route' => $route,
            'params' => $params,
            'fragment' => $fragment,
            'separator' => $separator,
            'script' => $script,
            'secure' => $secure,
        ));
    }
}