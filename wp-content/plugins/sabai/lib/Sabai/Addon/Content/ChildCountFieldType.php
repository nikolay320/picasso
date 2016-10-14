<?php
class Sabai_Addon_Content_ChildCountFieldType extends Sabai_Addon_Entity_ChildCountFieldType
{
    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        parent::__construct($addon, $name, 'content');
    }
}