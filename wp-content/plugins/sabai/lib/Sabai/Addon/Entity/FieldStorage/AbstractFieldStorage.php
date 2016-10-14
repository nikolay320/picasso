<?php
abstract class Sabai_Addon_Entity_FieldStorage_AbstractFieldStorage implements Sabai_Addon_Entity_IFieldStorage
{
    protected $_application, $_name;

    public function __construct(Sabai $application, $name)
    {
        $this->_application = $application;
        $this->_name = $name;
    }

    public function entityFieldStorageGetInfo($key = null)
    {
        $info = $this->_entityFieldStorageGetInfo();

        return isset($key) ? @$info[$key] : $info;
    }

    abstract protected function _entityFieldStorageGetInfo();
}