<?php
class Sabai_Addon_Content_ParentFieldWidget extends Sabai_Addon_Entity_ParentFieldWidget
{
    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        parent::__construct($addon, $name, 'content', 'content_parent');
    }
}