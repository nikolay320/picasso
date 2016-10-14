<?php
class Sabai_Helper_GravatarUrl extends Sabai_Helper
{
    public function help(Sabai $application, $email, $size = 96, $default = 'mm', $rating = null, $secure = false)
    {       
        $url = sprintf(
            '%s://www.gravatar.com/avatar/%s?s=%d&d=%s',
            $secure ? 'https' : 'http',
            md5(strtolower($email)),
            $size,
            urlencode($default)
        );
        if (isset($rating)) $url .= '&r=' . urlencode($rating);

        return $url;
    }
}