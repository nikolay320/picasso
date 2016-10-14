<?php
class Sabai_Platform_WordPress_UserIdentity extends Sabai_UserIdentity
{
    public function __construct($user, array $data = array(), $gravatarDefault = null, $gravatarRating = null)
    {
        $data += array(
            'name' => $user->display_name,
            'email' => $user->user_email,
            'url' => $user->user_url,
            'created' => strtotime($user->user_registered),
            'gravatar' => true,
            'gravatar_default' => isset($gravatarDefault) ? $gravatarDefault : get_option('avatar_default'),
            'gravatar_rating' => isset($gravatarRating) ? $gravatarRating : get_option('avatar_rating'),
        );
        parent::__construct($user->ID, $user->user_login, $data);
    }
}