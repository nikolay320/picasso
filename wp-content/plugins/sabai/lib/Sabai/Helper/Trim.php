<?php
class Sabai_Helper_Trim extends Sabai_Helper
{
    public function help(Sabai $application, $str)
    {
        return function_exists('mb_ereg_replace') && ($space = trim(_x(' ', 'whitspace', 'sabai'))) // get whitespace char for the current locale
            ? mb_ereg_replace("^($space| |\t|\n|\r|\0|\x0B)*|($space| |\t|\n|\r|\0|\x0B)*\$", '', $str)
            : trim($str);
    }
}