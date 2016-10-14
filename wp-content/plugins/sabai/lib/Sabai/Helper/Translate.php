<?php
class Sabai_Helper_Translate extends Sabai_Helper
{
    public function help(Sabai $application, $msgIdAndPackageName)
    {
        if (!strpos($msgIdAndPackageName, '||')) {
            return $msgIdAndPackageName;
        }
        $args = explode('||', $msgIdAndPackageName);
        
        return __($args[0], $args[1]);
    }
}