<?php
class Sabai_Addon_Content_ParentFieldType extends Sabai_Addon_Entity_ParentFieldType
{
    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        parent::__construct($addon, $name, 'content');
    }
}