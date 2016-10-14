<?php
class Sabai_Helper__t extends Sabai_Helper
{
    public function help(Sabai $application, $msgId, $packageName = 'sabai')
    {
        if (is_array($msgId)) {
            $msgId = array_shift($msgId);
        }
        return $msgId . '||' . $packageName;
    }
}