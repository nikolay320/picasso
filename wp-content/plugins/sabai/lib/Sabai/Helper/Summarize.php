<?php
class Sabai_Helper_Summarize extends Sabai_Helper
{
    public function help(Sabai $application, $text, $length = 0, $trimmarker = '...')
    {
        if (!strlen($text)) return '';
        
        $text = strip_tags(strtr($text, array("\r" => '', "\n" => ' ')));
        
        return empty($length) ? $text : mb_strimwidth($text, 0, $length, $trimmarker);
    }
}