<?php
class Sabai_Addon_Content_FeaturedFieldType extends Sabai_Addon_Entity_FeaturedFieldType
{
    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        parent::__construct($addon, $name, 'content');
    }
}