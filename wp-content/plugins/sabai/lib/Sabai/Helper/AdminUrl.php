<?php
class Sabai_Helper_AdminUrl extends Sabai_Helper
{
    public function help(Sabai $application, $route = '/', array $params = array(), $fragment = '', $separator = '&amp;')
    {
        return $application->Url(array(
            'route' => $route,
            'script' => 'admin',
            'params' => $params,
            'fragment' => $fragment,
            'separator' => $separator
        ));
    }
}