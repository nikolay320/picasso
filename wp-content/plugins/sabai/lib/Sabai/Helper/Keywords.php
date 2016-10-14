<?php
class Sabai_Helper_Keywords extends Sabai_Helper
{
    public function help(Sabai $application, $input, $minLength = null)
    {
        if (!isset($minLength)) $minLength = 3;
        $keywords = array();
        $input = $application->Trim($input);
        foreach ($application->Split($input, null, 15) as $keyword) {
            if ($quote_count = substr_count($keyword, '"')) { // check if any quotes
                $_keyword = explode('"', $keyword);
                if (isset($fragment)) { // has a phrase open but not closed?
                    $keywords[] = $fragment . ' ' . array_shift($_keyword);
                    unset($fragment);
                    if (!$quote_count % 2) {
                        // the last quote is not closed
                        $fragment .= array_pop($_keyword);
                    }
                } else {
                    if ($quote_count % 2) {
                        // the last quote is not closed
                        $fragment = array_pop($_keyword);
                    }
                }
                if (!empty($_keyword)) $keywords = array_merge($keywords, $_keyword);
            } else {
                if (isset($fragment)) { // has a phrase open but not closed?
                    $fragment .= ' ' . $keyword;
                } else {
                    $keywords[] = $keyword;
                }
            }
        }
        // Add the last unclosed fragment if any, to the list of keywords
        if (isset($fragment)) $keywords[] = $fragment;

        // Extract unique keywords that are not empty
        $keywords_passed = $keywords_failed = array();
        foreach ($keywords as $keyword) {
            if (($keyword = trim($keyword))
                && !isset($keywords_passed[$keyword])
                && !isset($keywords_failed[$keyword])
            ) {
                if (mb_strlen($keyword) >= $minLength) {
                    $keywords_passed[$keyword] = $keyword;
                } else {
                    $keywords_failed[$keyword] = $keyword;
                }
            }
        }

        return array($keywords_passed, $keywords_failed, $input);
    }
}