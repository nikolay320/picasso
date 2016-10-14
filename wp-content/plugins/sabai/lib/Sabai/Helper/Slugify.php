<?php
class Sabai_Helper_Slugify extends Sabai_Helper
{
    public function help(Sabai $application, $text, $separator = '-', $maxLength = 200)
    {
        if (!$strlen = mb_strlen($text)) {
            return $text;
        }
        // transliterate
        if (false === $slug = $application->Transliterate($text)) {
            // transliterate failed, return original but make sure the length does not exceed max limit
            return empty($maxLength) ? $text : mb_strcut($text, 0 ,$maxLength);
        }
        // replace non alnum chars with separator
        $slug = preg_replace('/\W+/', $separator, $slug);
        // return original if more than 20% of original text has been stripped
        if (strlen($slug) / $strlen < 0.8) {
            // make sure the length does not exceed max limit
            return empty($maxLength) ? $text : mb_strcut($text, 0 ,$maxLength);
        }
        // make sure the length does not exceed max limit
        if (!empty($maxLength)) {
            $slug = substr($slug, 0, $maxLength);
        }
        // trim
        $slug = trim($slug, $separator);
        
        return strtolower($slug);
    }
}