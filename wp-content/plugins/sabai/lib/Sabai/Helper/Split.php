<?php
class Sabai_Helper_Split extends Sabai_Helper
{
    public function help(Sabai $application, $str, $regex = null, $limit = -1)
    {
        if (function_exists('mb_ereg_replace')) {
            if (!isset($regex)) {
                $str = mb_ereg_replace(__(' ', 'sabai'), ' ', $str);
                $regex = '\s+'; // use space(s) as the separator if regular expression is not specified
            }
            
            return mb_split($regex, $str, $limit);
        }

        return preg_split(isset($regex) ? '/' . $regex . '/' : '/\s+/', $str, $limit);
    }
}