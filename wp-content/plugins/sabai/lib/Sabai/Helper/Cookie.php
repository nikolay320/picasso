<?php
class Sabai_Helper_Cookie extends Sabai_Helper
{
    public function help(Sabai $application, $name, $value = null, $expire = 0, $httpOnly = false)
    {
        if (isset($value)) {
            $platform = $application->getPlatform();
            return @setcookie($name, $value, $expire, $platform->getCookiePath(), $platform->getCookieDomain(), false, $httpOnly);
        }
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
}