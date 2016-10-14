<?php
class Sabai_Helper_FormTag extends Sabai_Helper
{
    public function help(Sabai $application, $url = null, $method = 'get', array $attributes = array())
    {
        $extra_html = array();
        if (isset($actionPath)) {
            $attributes['action'] = $application->Url($actionPath);
        }
        $attr = array();
        foreach ($attributes as $k => $v) {
            $attr[] = sprintf(' %s="%s"', $k, Sabai::h($v, ENT_COMPAT));
        }
        $params = array();
        parse_str(parse_url($application->Url($url)->set('separator', '&'), PHP_URL_QUERY), $params);
        foreach ($params as $param_name => $param_value) {
            if (is_array($param_value)) {
                foreach ($param_value as $_param_name => $_param_value) {
                    $extra_html[] = sprintf('<input type="hidden" name="%s[%s]" value="%s" />', Sabai::h($param_name), Sabai::h($_param_name), Sabai::h($_param_value));
                }
            } else {
                $extra_html[] = sprintf('<input type="hidden" name="%s" value="%s" />', Sabai::h($param_name), Sabai::h($param_value));
            }
        }

        printf('<form method="%s"%s>%s', $method, implode('', $attr), implode(PHP_EOL, $extra_html));
    }
}