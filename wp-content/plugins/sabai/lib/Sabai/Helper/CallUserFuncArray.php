<?php
class Sabai_Helper_CallUserFuncArray extends Sabai_Helper
{
    public function help(Sabai $application, $callback, array $params = array())
    {
        if (is_array($callback) && is_array(@$callback[1])) {
            $params = empty($params) ? $callback[1] : array_merge($params, $callback[1]);
            $callback = $callback[0];
        }

        return call_user_func_array($callback, $params);
    }
}