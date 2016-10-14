<?php
class Sabai_Addon_Questions_Helper_AnswersUrl extends Sabai_Helper
{
    public function help(Sabai $application, $question, $path = '')
    {
        $bundle = $application->Entity_Bundle($question);
        return $application->Entity_Url($question, '/' . $application->getAddon($bundle->addon)->getSlug('answers') . $path);
    }
}