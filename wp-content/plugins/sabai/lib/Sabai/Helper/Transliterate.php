<?php
class Sabai_Helper_Transliterate extends Sabai_Helper
{
    public function help(Sabai $application, $text)
    {
        if (false === $ret = iconv('utf-8', 'us-ascii//TRANSLIT', $text)) {
            return false;
        }
        
        // remove accents resulting from OSX iconv
        return str_replace(array('\'', '`', '^'), '', $ret);
    }
}