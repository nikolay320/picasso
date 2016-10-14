<?php
class Sabai_UserIdentity extends SabaiFramework_User_RegisteredIdentity
{
    public function __construct($id, $username, array $data = array())
    {
        $data += array(
            'id' => (int)$id,
            'username' => $username,
            'url' => '',
            'email' => '',
            'name' => $username,
            'created' => 0,
            'thumbnail_large' => '',
            'thumbnail_medium' => '',
            'thumbnail_small' => '',
        );
        parent::__construct($data);
    }
}