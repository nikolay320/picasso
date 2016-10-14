<?php
class Sabai_Addon_Content_ActivityFieldType extends Sabai_Addon_Entity_ActivityFieldType
{
    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        parent::__construct($addon, $name, 'content');
    }
}