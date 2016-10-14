<?php
interface Sabai_Addon_Entity_IFieldStorages
{
    public function entityGetFieldStorageNames();
    public function entityGetFieldStorage($name);
}