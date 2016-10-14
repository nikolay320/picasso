<?php
class Sabai_Helper_MainUrl extends Sabai_Helper
{
    public function help(Sabai $application, $route = '/', array $params = array(), $fragment = '', $separator = '&amp;')
    {
        return $application->Url(array(
            'route' => $route,
            'script' => 'main',
            'params' => $params,
            'fragment' => $fragment,
            'separator' => $separator
        ));
    }
}