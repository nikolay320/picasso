<?php
class Sabai_AnonymousUserIdentity extends SabaiFramework_User_AnonymousIdentity
{
    public function __construct($name, array $data = array())
    {
        $data += array(
            'url' => '',
            'email' => '',
            'created' => 0,
            'thumbnail_large' => '',
            'thumbnail_medium' => '',
            'thumbnail_small' => '',
        );
        parent::__construct(array('id' => 0, 'username' => '', 'name' => $name) + $data);
    }
    
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->_data)) {
            return;
        }
        $this->_data[$name] = $value;
    }
}